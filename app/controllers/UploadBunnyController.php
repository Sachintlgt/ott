<?php
@ini_set('max_execution_time', '0');
@ini_set('memory_limit', '2048M');
set_time_limit(0);

class UploadBunnyController extends BaseController
{
    public function upload_poster_image_16_9()
    {
        $file = Input::file('image_file');
        $video_id = Input::get('video_id');
        $img_file_name = '_poster_16_9_';
        $store_path = '';
        $db_field = 'custom_poster';
        return $this->upload_video_image($file, $video_id, $img_file_name, $store_path, $db_field);
    }

    public function upload_video_image($file, $video_id, $img_file_name, $store_path, $db_field)
    {
        $channel_id = BaseController::get_channel_id();
        if (empty($file) || empty($video_id)) {
            return Response::json(['status' => 'error', 'message' => 'Image file seems to be empty']);
        }
        $filename = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $supported_type = array('png', 'jpeg', 'jpg');
        if (!in_array($ext, $supported_type)) {
            return Response::json(['status' => 'error', 'message' => 'Only png, jpeg and jpg files are allowed to upload']);
        }
        //saving original file to bunny
        $path_name = $file->getPathName();
        $filenameWithoutExt = 'channel_' . $channel_id . $img_file_name . $video_id;
        $filename = $filenameWithoutExt . '.' . $ext;
        $path = $store_path;
        $video = Video::find($video_id);
        $response = $this->uploadFile($path_name, $filename, $path);
        if (($response == 201) || ($response == 200)) {
            $video = Video::find($video_id);
            $save_path = !empty($store_path) ? $store_path . '/' . $filename : $filename;
            $video->{$db_field} = $save_path;
            if ($db_field == 'custom_poster') {
                $video->primary_snapshot = 0;
            }
            $video->save();
            $save_path = $this->get_file_buuny_url($save_path) . '?time=' . strtotime(date('Y-m-d H:i:s'));
            return Response::json(['status' => 'success', 'message' => 'Image uploaded successfully.', 'url' => $save_path,], 200);
        }
        return Response::json(['status' => 'error', 'message' => 'Image not upload']);
    }

    public static function uploadFile($file_path_name, $filename, $path)
    {
        $channel_id = BaseController::get_channel_id();
        $channel = Channel::find($channel_id);
        if (empty($channel->bunnystorage) || empty($channel->bunnyfolder)) {
            $response = Response::json(['status' => 'error', 'message' => 'Bunny channel storage or folder not found please connect with admin!'], 404);
            return $response->getStatusCode();
        }
        if ($path === "bright_background") {
            $url = \Config::get('database.brightDataStorageUrl') . '/' . \Config::get('database.brightDataBunnystorage') . '/' . $channel->bunnyfolder . '/' . $path . '/' . $filename;
            $access_key = \Config::get('database.brightDataAccessKey');
        } else {
            $url = \Config::get('database.storageUrl') . '/' . $channel->bunnystorage . '/' . $channel->bunnyfolder . '/' . $path . '/' . $filename;
            $access_key = \Config::get('database.accessKey');
        }
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('PUT', $url, [
                'body' => \GuzzleHttp\Psr7\stream_for(fopen($file_path_name, 'r')),
                'headers' => [
                    'AccessKey' => $access_key,
                    'Content-Type' => 'application/octet-stream',
                ],
            ]);
            return $response->getStatusCode();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function get_file_buuny_url($path, $channel_id = '', $folder_path = '')
    {
        if (empty($channel_id)) {
            $channel_id = BaseController::get_channel_id();
        }
        $video = Video::where('channel_id', $channel_id)->where('thumbnail_name', 'LIKE', '%' . $path)->first();
        if (!empty($video) && $video->auto_import_videos == '1' && $video->primary_snapshot == 0) {
            $auto_import_bunny_details = AutoImportVideosBunnyDetails::where('channel_id', $video->channel_id)->first();
            $stream_url = $auto_import_bunny_details->bunny_stream_library_cdn_host;
            $url = $stream_url;
        } else if (strpos($path, 'bright_background') !== false || $folder_path === 'bright_background') {
            $url = \Config::get('database.brightDataImageCdn');
        } else {
            $url = \Config::get('database.imagecdn');
        }
        $channel = Channel::find($channel_id);

        $imageurl = empty($folder_path) ? $url . '/' . $channel->bunnyfolder . '/' . $path : $url . '/' . $channel->bunnyfolder . '/' . $folder_path . '/' . $path;

        if ((strpos($path, 'https://') !== false) || (strpos($path, 'http://') !== false)) {
            $url =  $path;
            $url = str_replace('https://s3.amazonaws.com/ott-playout/', \Config::get('database.imagecdn') . '/' . $channel->bunnyfolder . '/', $url);
        } else {
            $url =  $imageurl;
        }
        return $url;
    }
}