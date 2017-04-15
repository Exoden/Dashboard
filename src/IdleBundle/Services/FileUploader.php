<?php

namespace IdleBundle\Services;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload($filename, $dir, UploadedFile $file)
    {
        $fileName = $filename . '.' . $file->guessExtension();

        $file->move($this->targetDir . '/' . $dir, $fileName);

        return $fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }
}