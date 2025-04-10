

<?php

if (!function_exists('uploadFile')) {
    /**
     * Handle file upload.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $inputName
     * @param string $destinationPath
     * @return string|null
     */
    function uploadFile($request, $inputName, $index = null, $destinationPath = 'fileuploads')
    {
        if ($index !== null) {
            if ($request->hasFile($inputName) && isset($request->file($inputName)[$index])) {
                $file = $request->file($inputName)[$index];
                $fileName = time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($destinationPath), $fileName);
                return $fileName;
            }
        } else {
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($destinationPath), $fileName);
                return $fileName;
            }
        }

        return null;
    }
}


if (!function_exists('updateFile')) {
    /**
     * Handle file upload.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $inputName
     * @param string $destinationPath
     * @return string|null
     */
    function updateFile($request, $inputName, $destinationPath = 'fileuploads')
    {
        if ($request->hasFile($inputName)) {
            $fileName = time() . '.' . $request->file($inputName)->extension();
            $request->file($inputName)->move(public_path($destinationPath), $fileName);
            return $fileName;
        } else {
            return null;
        }
    }
}

if (!function_exists('fileget')) {
    /**
     * Generate the URL for a file upload.
     *
     * @param string $fileName
     * @param string $directory
     * @return string
     */
    function fileget($fileName, $directory = 'fileuploads')
    {
        if ($fileName == null) {
            $directory = 'assets/images';
            $fileName = 'notfound.jpg';
        }
        return asset($directory . '/' . $fileName);
    }
}

if (!function_exists('trimlinewith')) {
    /**
     * Trim the string to a specified number of words and append ellipsis if needed.
     *
     * @param string $string
     * @param int $word_limit
     * @return string
     */
    function trimlinewith($string, $word_limit = 2)
    {
        $words = explode(' ', $string);
        if (count($words) > $word_limit) {
            $trimmed = array_slice($words, 0, $word_limit);
            return implode(' ', $trimmed) . '...';
        }
        return $string;
    }
}

?>
