<?php
namespace app\common\controller;

use think\facade\Filesystem;

class Upload
{
    use Jump;

    /**
     * 上传文件
     * @access public
     * @return array
     */
    public function putFile($field = 'files', $type = '')
    {
        // 此时也可能报错
        // 比如上传的文件过大,超出了配置文件中限制的大小
        try {
            $files = request()->file($field);
            return $this->_upload($files, $type);
        } catch (\think\Exception $e) {
            return $this->result([], '1001', $this->_languageChange($e->getMessage()));
        }
    }
    /**
     * 执行文件上传
     * @access private
     * @return array
     */
    private function _upload($files, $type = '')
    {
        // 上传验证
        try {
            validate(
                [
                    'file' => [
                        // 限制文件大小(单位b)，这里限制为4M
                        'fileSize' => 4 * 1024 * 1024,
                        // 限制文件后缀，多个后缀以英文逗号分割
                        'fileExt' => 'jpg,jpeg,png,gif',
                    ],
                ],
                [
                    'file.fileSize' => '文件大小太大！',
                    'file.fileExt' => '非系统允许的上传格式！',
                ]
            )->check(['file' => $files]);
        } catch (\think\exception\ValidateException $e) {
            return $this->result([], '1003', $e->getMessage());
        }
        // 确定使用的磁盘
        $disks = Filesystem::getDefaultDriver();
        // 从存放目录开始的存放路径
        $file_url = null;
        if ($type == "") { // 上传本地
            // 文件存放目录名称
            $dir = 'image';
            $savename = Filesystem::disk($disks)->putFile($dir, $files, 'md5');
            $path = Filesystem::getDiskConfig($disks, 'url') . '/' . str_replace('\\', '/', $savename);
            $file_url = [
                'url' => $path,
                'id' => "local:$savename",
            ];
        } else { // 上传图床
            // 文件存放目录名称
            $dir = 'tmp';
            $savename = Filesystem::disk($disks)->putFileAs($dir, $files, $files->hash('md5'));
            $path = Filesystem::getDiskConfig($disks, 'root') . '/' . str_replace('\\', '/', $savename);
            $class = 'app\\common\\extend\\upload\\' . ucfirst($type);
            if (class_exists($class)) {
                $api = new $class;
                $res = $api->submit($path);
                $file_url['url'] = $res['url'];
                $file_url['id'] = ucfirst($type) . ":" . $res['id'];
            }
        }
        if (isset($file_url['id'])) {
            // 返回上传成功时的数组
            return $this->result($file_url, '1', '上传成功');
        } else {
            return $this->result([], 1004, '上传失败');
        }
    }
    /**
     * 英文转为中文
     * @access private
     */
    private function _languageChange($msg)
    {
        $data = [
            // 上传错误信息
            'unknown upload error' => '未知上传错误！',
            'file write error' => '文件写入失败！',
            'upload temp dir not found' => '找不到临时文件夹！',
            'no file to uploaded' => '没有文件被上传！',
            'only the portion of file is uploaded' => '文件只有部分被上传！',
            'upload File size exceeds the maximum value' => '上传文件大小超过了最大值！',
            'upload write error' => '文件上传保存错误！',
        ];

        return $data[$msg] ?? $msg;
    }

}
