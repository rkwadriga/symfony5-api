<?php declare(strict_types=1);
/**
 * Created 2021-10-04
 * Author Dmitry Kushneriov
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_login", methods={"POST"})
     */
    public function login(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser() ? $this->getUser() : null
        ]);
    }
}