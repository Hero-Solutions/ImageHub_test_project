<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 10/31/19
 * Time: 1:22 PM
 */

namespace App\Controller;


use App\Utils\Authenticator;
use SimpleSAML\Auth\Simple;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    /**
     * @Route("/imagehub/authenticate", name="authenticate")
     */
    public function authenticateAction(Request $request)
    {
        $adfsRequirement = $this->getParameter('adfs_requirements');

        // Forbidden by default
        $returnCode = 403;

        $auth = new Simple('default-sp');
        if ($auth->isAuthenticated()) {
            if(Authenticator::isAllowed($auth->getAttributes(), $adfsRequirement)) {
                // The user is authenticated and needs to be redirected to the original URL
                $returnCode = 302;
            }
        } else {
            $auth->requireAuth();
            if ($auth->isAuthenticated()) {
                if(Authenticator::isAllowed($auth->getAttributes(), $adfsRequirement)) {
                    // The user is now authenticated and needs to be redirected to the original URL
                    $returnCode = 302;
                }
            }
        }

        if($returnCode == 302) {
            $url = $request->query->get('url');
            return new Response('You are being redirected back to ' . $url, 302, array('Location' => $url));
        } else {
            return new Response('Sorry, you are not allowed to access this document.', 403);
        }
    }
}
