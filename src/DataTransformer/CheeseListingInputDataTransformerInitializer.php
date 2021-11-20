<?php declare(strict_types=1);
/**
 * Created 2021-11-18
 * Author Dmitry Kushneriov
 */

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingInputDataTransformerInitializer implements DataTransformerInitializerInterface
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    /**
     * @param CheeseListingInput $input
     * @param string $to
     * @param array $context
     *
     * @return CheeseListing
     */
    public function transform($input, string $to, array $context = [])
    {
        $this->validator->validate($input);

        return $input->createOrUpdateEntity($context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof CheeseListing) {
            // Already transformed
            return false;
        }
        return $to === CheeseListing::class && ($context['input']['class'] ?? null) === CheeseListingInput::class;
    }

    public function initialize(string $inputClass, array $context = [])
    {
        return CheeseListingInput::createFromEntity($context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null);
    }
}