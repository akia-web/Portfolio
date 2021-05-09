<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StageController extends AbstractController
{
    /**
     * @Route("/stage", name="stage")
     */
    public function index(ArticlesRepository $articleRepo, CategoriesRepository $categorieRep): Response
    {
        $categorie = $categorieRep -> findBy(['name'=>'Stage']); 

        return $this->render('stage/index.html.twig', [
            'articles' => $articleRepo->findBy(['active'=> true, 'categories'=>$categorie]),
        ]);
    }
}
