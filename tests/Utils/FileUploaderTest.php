<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests\Utils;

use AdrianBaez\Bundle\EasySfBundle\Utils\FileUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    /**
     * @var UploadedFile $uploadedFile
     */
    protected $uploadedFile;

    public function setUp()
    {
        parent::setUp();
        $this->uploadedFile = $this->createMock(UploadedFile::class);
    }

    public function testGetTargetDirectory()
    {
        $uploader = new FileUploader('/foo/bar');
        $this->assertEquals('/foo/bar', $uploader->getTargetDirectory());
    }

    public function testGenerateFilenameDefault()
    {
        $uploader = new FileUploader('/foo/bar');
        $pattern = '/^[a-f0-9]{32}\.baz$/';
        $this->uploadedFile->expects($this->once())->method('guessExtension')->will($this->returnValue('baz'));
        $this->assertRegexp($pattern, $uploader->generateFilename($this->uploadedFile));

    }

    public function testGenerateFilenamePersonalized()
    {
        $filenameGenerator = function($file, $arguments){
            return $arguments['baz'] . '.' . $file->guessExtension();
        };

        $this->uploadedFile->expects($this->once())->method('guessExtension')->will($this->returnValue('foo'));

        $uploader = new FileUploader('/bar', $filenameGenerator);

        $uploadedFilename = $uploader->upload($this->uploadedFile, ['baz' => 'qux']);
        $this->assertEquals('qux.foo', $uploadedFilename);

    }

    public function testUpload()
    {
        $targetDir = '/foo/bar';
        $pattern = '/^[a-f0-9]{32}\.baz$/';

        $this->uploadedFile->expects($this->once())->method('move')->with($targetDir, $this->matchesRegularExpression($pattern));
        $this->uploadedFile->expects($this->once())->method('guessExtension')->will($this->returnValue('baz'));

        $uploader = new FileUploader($targetDir);

        $uploadedFilename = $uploader->upload($this->uploadedFile);
        $this->assertRegexp($pattern, $uploadedFilename);
    }

    public function testUploadWithFilenameGenerator()
    {
        $targetDir = '/foo/bar';

        $filenameGenerator = function($file, $arguments){
            return $arguments['baz'] . '.' . $file->guessExtension();
        };

        $this->uploadedFile->expects($this->once())->method('move')->with($targetDir, 'quux.qux');
        $this->uploadedFile->expects($this->once())->method('guessExtension')->will($this->returnValue('qux'));

        $uploader = new FileUploader($targetDir, $filenameGenerator);

        $uploadedFilename = $uploader->upload($this->uploadedFile, ['baz' => 'quux']);
        $this->assertEquals('quux.qux', $uploadedFilename);
    }
}
