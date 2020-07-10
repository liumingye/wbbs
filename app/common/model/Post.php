<?php

namespace app\common\model;

use think\facade\Filesystem;

class Post extends Base
{
    protected $type = [
        'image' => 'serialize',
    ];
    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($post)
    {
        if (!empty($post->image)) {
            $image = [];
            foreach ($post->image as $img) {
                if ($img['type'] == "local") {
                    $disks = Filesystem::getDefaultDriver();
                    $image[] = (string) url(Filesystem::getDiskConfig($disks, 'url') . '/' . $img['id']);
                } elseif ($img['type'] == "Alibaba") {
                    $image[] = "https://ae01.alicdn.com/kf/{$img['id']}.jpg";
                }
            }
            $post->setAttr('image_format', $image);
        }
        if (!empty($post->text)) {
            $tag_pattern = "/\#([^\#|.]+)\#/";
            $text = $post->text;
            $text = preg_replace_callback($tag_pattern, function ($match) {
                return '<a href="' . url('/search', ['q' => "#$match[1]#"]) . '" target="_blank">#' . $match[1] . '#</a>';
            }, $text);
            $post->setAttr('text_format', $text);
        }
    }

    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }
}
