<?php


namespace App\Controller;


use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/api")
     */
    public function api()
    {
        return $this->render('base.html.twig');
    }


    /**
     * @Route("/api/products")
     */
    public function products(Request $request, SerializerInterface $serializer)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        return new JsonResponse('My Products');
    }
}
