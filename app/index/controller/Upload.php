<?php
namespace app\index\controller;

use app\common\controller\Upload as UploadController;

class Upload extends Base
{
    public function upload()
    {
        $upload = new UploadController;
        return $upload->putFile('files', 'Alibaba');
    }
}
