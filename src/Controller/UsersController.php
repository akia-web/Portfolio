<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Categories;
use App\Entity\Images;
use App\Entity\Users;
use App\Form\ArticlesType;
use App\Form\CategoriesType;
use App\Form\EditProfilType;
use App\Form\EditUseurType;
use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(ArticlesRepository $articlesRepo, UsersRepository $users): Response
    {
                return $this->render('admin/accueil/home.html.twig', [
            'controller_name' => 'UsersController',
            'articles' => $articlesRepo->findAll(),
            'users' => $users->findAll()
        ]);
    }

       /**
     * @Route("admin/categories/ajout", name="categories_ajout")
     */
    public function ajoutCategorie(Request $request, CategoriesRepository $catsRepo)
    {

        $categorie = new Categories;
        $form = $this->createForm(CategoriesType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/categories/ajout.html.twig', [
            'form' => $form->createView(),
            'categories' => $catsRepo->findAll()
        ]);
    }


    
       /**
     * @Route("admin/categories/modifier{id}", name="categories_modifier")
     */
    public function modifierCategorie(Request $request, Categories $categorie)
    {
        $form = $this->createForm(CategoriesType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/categories/modif.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/ajouter/article", name="admin_articles_ajout")
     */
    public function AjoutArticle(Request $request): Response
    {

        $article = new Articles;
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //on récupère les images transmises
            $images =  $form->get('images')->getData();

            // on boucle sur les images
            foreach($images as $image){
                //on génère un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                // on copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on stock le nom de l'image dans la bdd
                $img = new Images();
                $img->setName($fichier);
                $article->addImage($img);
            }

            $article->setUsers($this->getUser());
            $article->setActive(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/articles/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/admin/modifier/article{id}", name="admin_articles_modif")
    */
    public function modifierarticle(Request $request, Articles $article)
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
        //on récupère les images transmises
        $images =  $form->get('images')->getData();

        // on boucle sur les images
        foreach($images as $image){
            //on génère un nouveau nom de fichier
            $fichier = md5(uniqid()).'.'.$image->guessExtension();
            // on copie le fichier dans le dossier uploads
            $image->move(
                $this->getParameter('images_directory'),
                $fichier
            );
            // on stock le nom de l'image dans la bdd
            $img = new Images();
            $img->setName($fichier);
            $article->addImage($img);
        }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/articles/ajout.html.twig', [
            'form' => $form->createView(),
            'article' => $article
          
        ]);
    }

    /**
    * @Route("/admin/supprimer/image/{id}", name="article_supprimer_images", methods={"DELETE"})
    */
    public function supprimerImagesArticles(Images $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        // on vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            $nom = $image->getName();

            //on supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);
            // on supprime de la bdd
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token Invalid'], 400);
        };
    }

    /**
    * @Route("/admin/supprimer/article{id}", name="supprimer_article")
    */
    public function supprimerArticle(Articles $article)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        $this->addFlash('message', 'L\'article à bien été supprimé');
        return $this->redirectToRoute('admin');
        
    }


    /**
    * @Route("/admin/publier/{id}", name="activer")
    */
    public function publier(Articles $article)
    {
        $article->setActive(($article->getActive())?false:true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return new Response("true");
    }



    /**
     * @Route("/admin/edit/password", name="admin_edit_password")
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
            if($request->isMethod('POST')){
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();

                //On vérifie si les deux mots de passes sont identiques
                if($request->request->get('pass') == $request->request->get('pass2')){
                    $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass')));
                    $em->flush();
                    $this->addFlash('message', 'Mot de passe mis à jour');
                    return $this->redirectToRoute('admin');
                }else{
                    $this->addFlash('error', 'Les deux mots de passes ne sont pas identiques');
                }
            }
       
        return $this->render('admin/edit/password.html.twig');
    }

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function voirUsers(UsersRepository $users){
        return $this->render('admin/roles/roles.html.twig',[
            'users' => $users->findAll()
        ]);
    }
    
      /**
     * @Route("/admin/modifier/utilisateur/{id}", name="admin_modifier_utilisateur")
     */
    public function ModifUsers(Users $user, Request $request){
        $form = $this->createForm(EditUseurType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'L\'utilisateur a été modifié');
            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/roles/edit.html.twig',[
            'form' => $form->createView()
        ]);
    }
    
}
