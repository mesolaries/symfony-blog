<?php
/**
 * User: Emil
 * Date: 24/06/2020
 * Time: 1:10 am
 */

namespace App\Transformer;


use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;

class TagToStringTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function transform($tags)
    {
        if (null === $tags) {
            return '';
        }

        $tags = $tags->toArray();
        return implode(', ', $tags);
    }

    /**
     * @param mixed $string
     *
     * @return Tag[]|mixed|object[]|void
     */
    public function reverseTransform($string)
    {
        $names = array_unique(array_filter(array_map('trim', explode(',', $string))));
        $tags = $this->em->getRepository(Tag::class)->findBy([
            'name' => $names
        ]);

        $newNames = array_diff($names, $tags);
        foreach ($newNames as $name) {
            $tag = new Tag();
            $tag->setName(trim($name));
            $this->em->persist($tag);
            $tags[] = $tag;
        }
        $this->em->flush();

        return new PersistentCollection($this->em, $this->em->getClassMetadata(Tag::class), new ArrayCollection($tags));
    }
}