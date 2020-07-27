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
            /* 话题处理 */
            if (strpos($text, '#') !== false) {
                if (preg_match_all('~\#([^\#]+?)\#~', $text, $match)) {
                    $tags = $match[1];
                    foreach ($match[1] as $v) {
                        $cont_sch[] = "#{$v}#";
                        $cont_rpl[] = "[T]{$v}[/T]";
                    }
                }
                if ($cont_sch && $cont_rpl) {
                    // 增加话题数据
                    $topics = array_unique($tags);
                    $topicid = [];
                    foreach ($topics as $v) {
                        $topic = new Topic;
                        $res = $topic->field('id')->where('name', $v)->find();
                        if (!$res) {
                            $topic->save([
                                'name' => $v,
                            ]);
                            $topicid[] = $topic->id;
                        } else {
                            $topicid[] = $res->id;
                        }
                    }
                    $cont_sch = array_unique($cont_sch);
                    $cont_rpl = array_unique($cont_rpl);
                    // 按照长度排序，防止被错误替换
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
            /** 图片处理 */
            if (isset($image) && $image) {
                $images = explode("|", $image);
                if (!empty($images)) {
                    $image = [];
                    foreach ($images as $img) {
                        list($type, $id) = explode(":", $img);
                        if (isset($type) && in_array($type, config('wbbs.upload.way')) && isset($id)) {
                            $text .= "[F]{$type}:{$id}[/F]";
                        }
                    }
                }
            }
            $data['text'] = $text;
            $res = $this->save($data);
            if ($res) {
                // 文章 关联 话题
                if (!empty($topicid)) {
                    $arr = [];
                    $tids = [];
                    foreach ($topicid as $tid) {
                        $arr[] = [
                            'pid' => $this->id,
                            'tid' => $tid,
                        ];
                        $tids[] = $tid;
                    }
                    // 增加关联数据
                    $relationships = new Relationships;
                    $relationships->saveAll($arr);
                    // 增加话题讨论数
                    $topic = new Topic;
                    $topic->where('id', 'IN', $tids)->inc('talks')->update();
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
            // 减少话题讨论数
            $tid = Relationships::field('tid')->where('pid', $id)->select()->toArray();
            if (!empty($tid)) {
                $tid = array_column($tid, 'tid');
                Topic::where('id', 'IN', $tid)->dec('talks')->update();
            }
            // 减少发帖数
            User::find($uid)->dec('post_num')->cache('user_' . $uid)->update();
            return ['code' => 1, 'msg' => '删除成功'];
        }
        return ['code' => 0, 'msg' => '删除失败'];
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
