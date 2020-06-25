<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig', [

        ]);
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function userComments(): Response
    {
        return $this->render('user/comments.html.twig', [

        ]);
    }

    /**
     * @Route("/notes", name="user_notes", methods={"GET"})
     */
    public function userNotes(): Response
    {
        return $this->render('user/notes.html.twig', [

        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request ,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            //**********************************IMAGE UPLOAD************************************

            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'), //in Services.yaml defined folder for uploaded image
                        // services.yaml->config
                        $fileName

                    );
                } catch (FileException $e){

                }
                $user->setImage($fileName);
            }

//***********************************************************************************
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,$id, User $user): Response
    {

        $user=$this->getUser();
        if ($user->getId()!=$id){
            return $this->redirectToRoute('home_home');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            //**********************************IMAGE UPLOAD************************************

            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'), //in Services.yaml defined folder for uploaded image
                        // services.yaml->config
                        $fileName

                    );
                } catch (FileException $e){

                }
                $user->setImage($fileName);
            }

            //***********************************************************************************

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @return string
     */

    private function generateUniqueFileName()
    {
        //md5()reduces the similarity of the file names generated by
        //uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
