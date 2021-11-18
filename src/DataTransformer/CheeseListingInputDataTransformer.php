<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

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
        $cheeseListing = new CheeseListing($input->title);
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