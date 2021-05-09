<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(ArticlesRepository $articleRepo, CategoriesRepository $categorieRep): Response
    {
        $categorie = $categorieRep -> findBy(['name'=>'Projets']); 

        return $this->render('main/accueil.html.twig', [
            'articles' => $articleRepo->findBy(['active'=> true, 'categories'=>$categorie]),
        ]);
    }

       /**
     * @Route("/article/{slug}", name="article")
     */
    public function articles($slug, ArticlesRepository $articleRepo): Response
    {
        $article = $articleRepo->findOneBy(['slug' => $slug]);

        if (!$article){
            throw new NotFoundHttpException('Pas d\'article trouvé');
        }
        return $this->render('main/article.html.twig', compact('article'));
    
    }
}
