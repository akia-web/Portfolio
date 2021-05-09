<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AutresController extends AbstractController
{
    /**
     * @Route("/autres", name="autres")
     */
    public function index(ArticlesRepository $articleRepo, CategoriesRepository $categorieRep): Response
    {
        $categorie = $categorieRep -> findBy(['name'=>'Autres']); 

        return $this->render('autres/index.html.twig', [
            'articles' => $articleRepo->findBy(['active'=> true, 'categories'=>$categorie]),
        ]);
    }
}
