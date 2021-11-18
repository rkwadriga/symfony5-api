<?php declare(strict_types=1);
/**
 * Created 2021-11-17
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\CheeseListingOutput;
use App\Entity\CheeseListing;

class CheeseListingOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param CheeseListing $cheeseListing
     * @param string $to
     * @param array $context
     *
     * @return CheeseListingOutput
     */
    public function transform($cheeseListing, string $to, array $context = [])
    {
        return CheeseListingOutput::createFromEntity($cheeseListing);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof CheeseListing && $to === CheeseListingOutput::class;
    }
}