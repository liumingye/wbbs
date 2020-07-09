<?php
namespace app\index\controller;

use app\common\controller\Upload as UploadController;

class Upload extends Base
{
    use \app\common\controller\Jump;
    public function upload()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        $upload = new UploadController;
        return $upload->putFile('file', 'Alibaba');
    }
}
