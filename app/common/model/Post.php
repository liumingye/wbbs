<?php

namespace app\common\model;

use think\facade\Filesystem;

class Post extends Base
{
    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($post)
    {
        $post->setAttr('text_format', self::parse($post->text));
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
     * 内容处理
     */
    public function handle($content, $image, $isret = 1)
    {
        /* 话题处理 */
        if (strpos($content, '#') !== false) {
            if (preg_match_all('~\#([^\#]+?)\#~', $content, $match)) {
                foreach ($match[1] as $v) {
                    $v = str_replace(array('(', ')'), '', trim($v));
                    $tags[$v] = $v;
                    $cont_sch[] = "#{$v}#";
                    $cont_rpl[] = "[T]{$v}[/T]";
                    $topics[] = $v;
                }
            }
            //update topic
            if ($isret == 1) {
                $topics = array_unique($topics);
                $db = new Topic;
                foreach ($topics as $v) {
                    $topic = $db->where('name', $v)->find();
                    if (!$topic) {
                        $db->name = $v;
                        $db->talks = 1;
                        $db->save();
                        $topicid[] = $db->id;
                    } else {
                        $db->where('name', $v)->inc('talks');
                        $topicid[] = $topic->id;
                    }
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
                $content = str_replace($cont_sch, $cont_rpl2, $content);
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
                        $content .= "[F]{$type}:{$id}[/F]";
                    }
                }
            }
        }
        return $content;
    }

    /**
     * 内容解析
     */
    public static function parse($text)
    {
        /*$p = array(
        );
        $r = array(
        );
        $text = preg_replace($p, $r, $text);*/
        // 话题
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
        return compact('text', 'image');
    }
}
