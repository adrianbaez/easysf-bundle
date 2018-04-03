<?php

namespace AdrianBaez\Bundle\EasySfBundle\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var callable $filenameGenerator
     */
    private $filenameGenerator;

    /**
     * @param string $targetDirectory
     * @param callable|null $filenameGenerator
     */
    public function __construct($targetDirectory, callable $filenameGenerator = null)
    {
        $this->targetDirectory = $targetDirectory;
        $this->filenameGenerator = $filenameGenerator;
    }

    /**
     * @param  UploadedFile $file
     * @param  array        $arguments
     * @return string
     */
    public function upload(UploadedFile $file, $arguments = [])
    {
        $fileName = $this->generateFilename($file, $arguments);
        $file->move($this->getTargetDirectory(), $fileName);
        return $fileName;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @param  UploadedFile $file
     * @param  array        $arguments
     * @return string
     */
    public function generateFilename(UploadedFile $file, $arguments = [])
    {
        if (null !== $this->filenameGenerator){
            return call_user_func($this->filenameGenerator, $file, $arguments);
        }
        return md5(uniqid()).'.'.$file->guessExtension();
    }
}
