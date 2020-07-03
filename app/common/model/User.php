<?php

namespace app\common\model;

class User extends Base
{
    public static function onAfterRead($user)
    {
        if ($user->avatar) {
            $id = $user->uid ? $user->uid : $user->id;
            $dir = substr(sprintf("%09d", $id), 0, 3);
            $avatar = config('wbbs.upload_url') . "/avatar/$dir/$id.png?" . $user->avatar;
        } elseif ($user->email) {
            $avatar = 'https://gravatar.helingqi.com/avatar/' . md5($user->email);
        } else {
            $avatar = config('view.tpl_replace_string.__STATIC__') . '/img/avatar.png';
        }
        $user->setAttr('avatar', $avatar);
    }
}
