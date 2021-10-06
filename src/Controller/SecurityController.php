<?php declare(strict_types=1);
/**
 * Created 2021-10-04
 * Author Dmitry Kushneriov
 */

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_login", methods={"POST"})
     */
    public function login(IriConverterInterface $iriConverter): Response
    {
        if (!$this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)) {
            return $this->json([
                'error' => 'Invalid request type. Only "application/json" is supported.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $iriConverter->getIriFromItem($this->getUser())
        ]);
    }
}