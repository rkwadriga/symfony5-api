<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class CheeseListingInputDataTransformer implements DataTransformerInterface
{
    /**
     * @param CheeseListingInput $input
     * @param string $to
     * @param array $context
     *
     * @return CheeseListing
     */
    public function transform($input, string $to, array $context = [])
    {
        $objectIndex = AbstractNormalizer::OBJECT_TO_POPULATE;
        $cheeseListing = ($context[$objectIndex] ?? null) instanceof CheeseListing
            ? $context[$objectIndex]
            : new CheeseListing();

        if ($input->title !== null) {
            $cheeseListing->setTitle($input->title);
        }
        if ($input->price !== null) {
            $cheeseListing->setPrice($input->price);
        }
        if ($input->description !== null) {
            $cheeseListing->setDescription($input->description);
        }
        if ($input->isPublished !== null) {
            $cheeseListing->setIsPublished($input->isPublished);
        }
        if ($input->owner !== null) {
            $cheeseListing->setOwner($input->owner);
        }

        return $cheeseListing;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CheeseListing) {
            // Already transformed
            return false;
        }
        return $to === CheeseListing::class && ($context['input']['class'] ?? null) === CheeseListingInput::class;
    }
}