<?php

namespace App\Transformer;


use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class StringToFileTransformer implements DataTransformerInterface
{

    private EntityManagerInterface $em;
    private FileUploader $fl;

    public function __construct(EntityManagerInterface $em, FileUploader $fl)
    {
        $this->em = $em;
        $this->fl = $fl;
    }


    /**
     * @param mixed $string
     *
     * @return mixed|File
     */
    public function transform($string)
    {
        if (is_file($this->fl->getTargetDirectory() . '/' . $string)) {
            return new File($this->fl->getTargetDirectory() . '/' . $string);
        }
        return $string;
    }

    /**
     * @param File $file
     *
     * @return mixed|string
     */
    public function reverseTransform($file)
    {
        // if (null === $file) {
        //     return null;
        // }
        //
        // return $file->getFilename();
        return $file;
    }
}