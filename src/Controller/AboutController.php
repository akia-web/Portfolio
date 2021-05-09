<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * @Route("/about", name="about")
     */
    public function index(ArticlesRepository $articleRepo, CategoriesRepository $categorieRep): Response
    {
        $categorie = $categorieRep -> findBy(['name'=>'about']); 

        return $this->render('about/index.html.twig', [
            'articles' => $articleRepo->findBy(['active'=> true, 'categories'=>$categorie]),
        ]);
    }
}
