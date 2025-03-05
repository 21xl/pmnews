<?php
/**
 * Script version 1.0.0
 *
 * @param $upload
 * @param $context
 *
 * @return mixed|string[]
 *
 */

function bt_webp_converter($upload, $context)
{

	$file = $upload['file'];
	$type = $upload['type'];


	if (strtolower($type) === 'image/gif') {
		return $upload;
	}

	$im = null;
	switch (strtolower($type)) {
		case 'image/jpeg':
		case 'image/jpg':
			$im = @imagecreatefromjpeg($file);
			break;
		case 'image/png':
			$im = @imagecreatefrompng($file);
			break;
	}

	if (is_null($im)) {
		return $upload;
	}

	$path_info = pathinfo($file);

	$new_slug = $path_info['filename'];

	// ----------- refactoring $new_slug

	$slug_arr = preg_split('|[\-\_\s \.]|iu', $new_slug, -1, 1);

	$new_slug = mb_strtolower(implode('-', $slug_arr), 'UTF-8');

	// translit
	$new_slug = bt_webp_transliterated_name($new_slug);
	$new_slug = preg_replace('#[\s_]#iu', '-', $new_slug);

	$result_filename = $new_slug . '.webp';

	imagepalettetotruecolor($im);
	imagealphablending($im, true);
	imagesavealpha($im, true);

	$dist_dir = $path_info['dirname'];
	$dest_file_path = $dist_dir . '/' . $result_filename;

	if (!!(file_exists($dest_file_path) && is_file($dest_file_path))) {
		$i = 1;
		$ok = true;

		$new_slug = preg_replace('|(-\d{1,2})$|iu', '', $new_slug);

		while ($ok) {
			$result_filename = $new_slug . '-' . $i . '.webp';
			$dest_file_path = '/' . $dist_dir . '/' . $result_filename;

			if (!(file_exists($dest_file_path) && is_file($dest_file_path))) {
				$ok = false;
				break;
			}
			$i++;
		}
	}

	imagewebp($im, $dest_file_path, 90);
	imagedestroy($im);

	@unlink($file);

	$uploads_obj = wp_upload_dir();
	$upload_dir_url = $uploads_obj['url'];
	$upload_url = $upload_dir_url . '/' . $result_filename;

	$new_upload = [
		'file' => $dest_file_path,
		'url' => $upload_url,
		'type' => 'image/webp',
	];

	return $new_upload;

}

add_filter('wp_handle_upload', 'bt_webp_converter', 10, 2);


function bt_webp_transliterated_name($income_text)
{
	$income_text = mb_strtolower($income_text, "UTF-8");
	$income_text = preg_replace("#[\s_]+#iu", "-", $income_text);
	$cyr = [
		'а',
		'б',
		'в',
		'г',
		'д',
		'е',
		'ё',
		'ж',
		'з',
		'и',
		'й',
		'к',
		'л',
		'м',
		'н',
		'о',
		'п',
		'р',
		'с',
		'т',
		'у',
		'ф',
		'х',
		'ц',
		'ч',
		'ш',
		'щ',
		'ы',
		'э',
		'ю',
		'я',
		'і',
		'ї',
		'є',
		'ь',

		'ґ',
		'ё',
		'ъ',
		'ы',
	];
	$lat = [
		'a',
		'b',
		'v',
		'g',
		'd',
		'e',
		'yo',
		'zh',
		'z',
		'y',
		'yi',
		'k',
		'l',
		'm',
		'n',
		'o',
		'p',
		'r',
		's',
		't',
		'u',
		'f',
		'h',
		'ts',
		'ch',
		'sh',
		'sch',
		'y',
		'e',
		'yu',
		'ya',
		'i',
		'yi',
		'ye',
		'',

		'g',
		'yo',
		'',
		'y',
	];
	$result = str_replace($cyr, $lat, mb_strtolower($income_text, 'UTF-8'));

	return trim($result);
}
?>