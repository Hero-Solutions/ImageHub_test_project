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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthCheckController extends AbstractController
{
    /**
     * @Route("/imagehub/authcheck", name="authcheck")
     */
    public function authenticateAction(Request $request)
    {
        $adfsRequirement = $this->getParameter('adfs_requirements');

        // Forbidden by default
        $returnCode = 403;

        $auth = new Simple('default-sp');
        if ($auth->isAuthenticated()) {
            if(Authenticator::isAllowed($auth->getAttributes(), $adfsRequirement)) {
                // The user is already authenticated, everything is in order
                $returnCode = 200;
            }
        } else {
            // The user is not yet authenticated, send them to the login page
            $returnCode = 302;
        }

        if($returnCode == 200) {
            return new Response('', $returnCode);
        } else if($returnCode == 302) {
            $url = $this->generateUrl('authenticate', array(), UrlGeneratorInterface::ABSOLUTE_URL);
            return new Response('You are being redirected to ' . $url, $returnCode, array('Location' => $url));
        } else {
            return new Response('Sorry, you are not allowed to access this document.', 403);
        }
    }
}
