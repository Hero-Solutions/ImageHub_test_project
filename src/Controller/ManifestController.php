<?php

namespace App\Controller;

use App\Entity\IIIfManifest;
use App\Utils\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManifestController extends AbstractController
{
    /**
     * @Route("/iiif/2/{manifestId}/manifest.json", name="manifest")
     */
    public function manifestAction(Request $request, $manifestId = '')
    {
        // Make sure the service URL name ends with a trailing slash
        $baseUrl = rtrim($this->getParameter('service_url'), '/') . '/';
        $manifest = $this->get('doctrine')->getRepository(IIIfManifest::class)->findOneBy(['manifestId' => $baseUrl . $manifestId . '/manifest.json']);
        if($manifest == null) {
            return new Response('Sorry, the requested document does not exist.', 404);
        } else {
            $authenticated = true;
            $whitelist = $this->getParameter('authentication_whitelist');
            $whitelisted = false;
            if($request->getClientIp() != null) {
                if(in_array($request->getClientIp(), $whitelist)) {
                    $whitelisted = true;
                    $authenticated = true;
                }
            }

            $data = json_decode($manifest->getData(), true);
            if(!$whitelisted) {
                if (array_key_exists('service', $data)) {
                    if (array_key_exists('@id', $data['service'])) {
                        if (strpos($data['service']['@id'], 'auth') > -1) {
                            $authenticated = false;
                        }
                    }
                }
            }
            if(!$authenticated) {
                // Authenticate the user through the AD FS with SAML
                if (Authenticator::authenticate($this->getParameter('adfs_requirements'))) {
                    $authenticated = true;
                }
            }
            if(!$authenticated) {
                return new Response('Sorry, you are not allowed to access this document.');
            } else {
                $headers = array(
                    'Content-Type' => 'application/json',
                    'Access-Control-Allow-Origin' => '*'
                );
                return new Response(json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), 200, $headers);
            }
        }
    }
}
