<?php declare(strict_types=1);
/**
 * Created 2021-10-11
 * Author Dmitry Kushneriov
 */

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Security\SecurityHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AdminGroupsContextBuilder
 *
 * * Should Be described in configuration in "services" block (look at the config/services.yaml:27)
 *
 * @package App\Serializer
 */
class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private SerializerContextBuilderInterface $decorated,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $context['groups'] = $context['groups'] ?? [];

        if ($this->authorizationChecker->isGranted(SecurityHelper::ROLE_ADMIN)) {
            $context['groups'][] = $normalization ? 'admin:read' : 'admin:write';
        }

        $context['groups'] = array_unique($context['groups']);

        return $context;
    }
}