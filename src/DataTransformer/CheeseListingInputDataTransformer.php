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
     * @param CheeseListingInput $cheeseListingInput
     * @param string $to
     * @param array $context
     *
     * @return CheeseListing
     */
    public function transform($cheeseListingInput, string $to, array $context = [])
    {
        return new CheeseListing();
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $to === CheeseListing::class;
    }
}