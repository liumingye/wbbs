<?php

namespace app\common\model;

use app\common\util\HyperDown;
use app\index\validate\Post as PostValidate;
use think\exception\ValidateException;
use think\facade\Filesystem;

class Post extends Base
{
    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($post)
    {
        $post->setAttr('format', self::parse($post->text));
    }

    /**
     * 关联 用户表
     */
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }

    /**
     * 关联 评论表
     */
    public function comment()
    {
        return $this->hasMany('Comment', 'pid');
    }

    /**
     * 列出文章
     */
    public function listData($where, $page = 1, $length = 10, $start = 0, $order = "create_time DESC, id DESC", $field = '*')
    {
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $length;
        $total = $this->where($where)->count();
        $list = $this->with('user')->withCache(60)->where($where)->limit($start, $length)->order($order)->select();
        $data = compact('list', 'page', 'length', 'total');
        return $data;
    }

    /**
     * 增加文章
     */
    public function saveData($data)
    {
        try {
            /** 初始化验证类 */
            $validate = validate(PostValidate::class);
            $validate->check($data);
            $text = htmlspecialchars(removeXSS(trim($data['text'])));
            $image = htmlspecialchars(removeXSS(trim($data['image'])));
            $data['text'] = $this->handle($text, $image);
            $res = $this->save($data);
            if ($res) {
                // 文章 关联 话题
                if (!empty($topicid)) {
                    $relationships = new Relationships;
                    $data = [];
                    foreach ($topicid as $tid) {
                        $data[] = [
                            'pid' => $this->id,
                            'tid' => $tid,
                        ];
                    }
                    $relationships->saveAll($data);
                }
                // 增加发帖数
                $uid = $data['uid'];
                User::find($uid)->inc('post_num')->cache('user_' . $uid)->update();
                return ['code' => 1, 'msg' => '发布成功'];
            }
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getError()];
        }
        return ['code' => 0, 'msg' => '发布失败'];
    }

    /**
     * 删除文章
     */
    public function delData($data)
    {
        $id = $data['id'];
        $uid = $data['uid'];
        $res = $this->where(['id' => $id, 'uid' => $uid])->cache('post_' . $id)->delete();
        if ($res) {
            // 删除评论
            $comment = new Comment;
            $comment->where('pid', $id)->delete();
            return ['code' => 1, 'msg' => '删除成功'];
        }
        return ['code' => 0, 'msg' => '删除失败'];
    }

    /**
     * 内容处理
     */
    public function handle($text, $image)
    {
        /* 话题处理 */
        if (strpos($text, '#') !== false) {
            if (preg_match_all('~\#([^\#]+?)\#~', $text, $match)) {
                foreach ($match[1] as $v) {
                    $v = str_replace(array('(', ')'), '', trim($v));
                    $tags[$v] = $v;
                    $cont_sch[] = "#{$v}#";
                    $cont_rpl[] = "[T]{$v}[/T]";
                    $topics[] = $v;
                }
            }
            if (!empty($topics)) {
                //update topic
                $topics = array_unique($topics);
                $db = new Topic;
                foreach ($topics as $v) {
                    $topic = $db->where('name', $v)->find();
                    if (!$topic) {
                        $db->save([
                            'name' => $v,
                            'talks' => 1,
                        ]);
                        $topicid[] = $db->id;
                    } else {
                        $db->where('name', $v)->inc('talks');
                        $topicid[] = $topic->id;
                    }
                }
                if ($cont_sch && $cont_rpl) {
                    $cont_sch = array_unique($cont_sch);
                    $cont_rpl = array_unique($cont_rpl);
                    //按照长度排序，防止被错误替换
                    uasort($cont_sch, function ($a, $b) {
                        return strLen($a) < strLen($b);
                    });
                    foreach ($cont_sch as $key => $val) {
                        $cont_rpl2[$key] = $cont_rpl[$key];
                    }
                    $cont_sch = array_merge($cont_sch);
                    $cont_rpl2 = array_merge($cont_rpl2);
                    $text = str_replace($cont_sch, $cont_rpl2, $text);
                }
            }
        }
        /** 图片处理 */
        if (isset($image) && $image) {
            $date_image = explode("|", $image);
            if (!empty($date_image)) {
                $image = [];
                foreach ($date_image as $img) {
                    list($type, $id) = explode(":", $img);
                    if (isset($type) && in_array($type, config('wbbs.upload.way')) && isset($id)) {
                        $text .= "[F]{$type}:{$id}[/F]";
                    }
                }
            }
        }
        return $text;
    }

    /**
     * 内容解析
     */
    public static function parse($text)
    {
        $text = preg_replace_callback('/\[T\](.*?)\[\/T\]/i', function ($match) {
            return '<a href="' . url('/search', ['q' => "#$match[1]#"]) . '" target="_blank">#' . $match[1] . '#</a>';
        }, $text);
        // 图片
        $image = [];
        $text = preg_replace_callback('/\[F](.*?)\[\/F\]/i', function ($match) use (&$image) {
            list($type, $id) = explode(':', $match[1]);
            if ($type == "local") {
                $disks = Filesystem::getDefaultDriver();
                $image[] = (string) url(Filesystem::getDiskConfig($disks, 'url') . '/' . $id);
            } elseif ($type == "Alibaba") {
                $image[] = "https://ae01.alicdn.com/kf/$id.jpg";
            }
            return '';
        }, $text);
        // markdown
        $parser = new HyperDown();
        $parser->enableHtml(false);
        $text = $parser->makeHtml($text);
        // 使用br换行
        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $text);
        return compact('text', 'image');
    }
}
