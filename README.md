Ott is the streaming platform for anyone to stream unlimited movies. Ott is a streaming service that delivers video content over different devices such as (smart TV, Apple TV, Android,etc )
➤ Cloud LIve Playout
➤ SSAI
➤ Graphic Ad Insertion TV
➤ OTT Streaming
➤ Recording
➤ Shifting
➤ Synching
➤ Monitoring
➤ EPG
➤ Captioning
➤ SCTE35 / SCTE104

# UploadBunnyController

This controller handles image uploads for videos in a media management platform using BunnyCDN. It provides a clean API to upload poster images (with 16:9 aspect ratio) and stores the image path in the video model database.

---

## Features

- Upload and validate image files (`jpeg`, `jpg`, `png`) for videos.
- Automatically saves images to BunnyCDN via HTTP PUT.
- Updates the video record in the database with the image path.
- Generates CDN URLs for accessing uploaded images.
- Supports custom folder paths and bright background handling.

---

## Requirements

- Laravel (assumes usage of Input, Response, Eloquent, and Config)
- Guzzle HTTP Client (`guzzlehttp/guzzle`)
- BunnyCDN storage configuration

---

## Public Methods

### `upload_poster_image_16_9()`

Handles an HTTP request to upload a 16:9 poster image. Validates the request and delegates to `upload_video_image`.

**Input:**
- `image_file`: (multipart file) The image file to upload.
- `video_id`: (string|int) The ID of the video to attach the image to.

**Returns:**
- JSON response with `status`, `message`, and `url` (on success).

---

### `upload_video_image($file, $video_id, $img_file_name, $store_path, $db_field)`

Performs the core image upload operation:
- Validates file extension
- Generates the image filename
- Calls BunnyCDN upload method
- Saves the image URL to the corresponding video record

**Arguments:**
- `$file`: Uploaded file
- `$video_id`: Video ID
- `$img_file_name`: String used as a part of the filename
- `$store_path`: (optional) BunnyCDN folder path
- `$db_field`: Name of the database field to update

---

### `uploadFile($file_path_name, $filename, $path)`

Uploads the file to BunnyCDN using a `PUT` request.

**Arguments:**
- `$file_path_name`: Local path to the file
- `$filename`: Name to save the file as on BunnyCDN
- `$path`: Subfolder in BunnyCDN

**Returns:**
- HTTP status code or error message

---

### `get_file_buuny_url($path, $channel_id = '', $folder_path = '')`

Constructs and returns a public CDN-accessible URL for the uploaded image.

**Arguments:**
- `$path`: Relative path or full image name
- `$channel_id`: Optional override for channel ID
- `$folder_path`: Optional override for Bunny folder path

**Returns:**
- Fully qualified image URL
