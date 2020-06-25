<?php

namespace App\Controller\Admin;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/note")
 */
class NoteController extends AbstractController
{
    /**
     * @Route("/", name="admin_note_index", methods={"GET"})
     */
    public function index(NoteRepository $noteRepository): Response
    {
        return $this->render('admin/note/index.html.twig', [
            'notes' => $noteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_note_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $note = new Note();
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
//**********************************IMAGE UPLOAD************************************

            $file = $form['image']->getData();
            if ($file) {
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'), //in Services.yaml defined folder for uploaded image
                        // services.yaml->config
                        $fileName

                    );
                } catch (FileException $e) {

                }
                $note->setImage($fileName);
            }

//***********************************************************************************
            //******************** pdf upload************************************
            $addednote = $form['added_note']->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($addednote) {
                $originalFilename = $this->generateUniqueFileName() . '.' . $addednote->guessExtension();

                try {
                    $addednote->move(
                        $this->getParameter('pdf_directory'),
                        $originalFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $note->setAddedNote($originalFilename);
            }
//*******************************************************************************************************

            // ... persist the $product variable or any other work
            $entityManager->persist($note);
            $entityManager->flush();
            return $this->redirectToRoute('admin_note_index');


    }



        return $this->render('admin/note/new.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }





    /**
     * @Route("/{id}", name="admin_note_show", methods={"GET"})
     */
    public function show(Note $note): Response
    {
        return $this->render('admin/note/show.html.twig', [
            'note' => $note,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_note_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Note $note): Response
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);
//********************************** Image Upload*********************************
        if ($form->isSubmitted() && $form->isValid()) {
            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName

                    );
                } catch (FileException $e){

                }
                $note->setImage($fileName);
            }

//**********************************************************************************
            //******************** pdf upload************************************
            $addednote = $form['added_note']->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($addednote) {
                $originalFilename = $this->generateUniqueFileName() . '.' . $addednote->guessExtension();

                try {
                    $addednote->move(
                        $this->getParameter('pdf_directory'),
                        $originalFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $note->setAddedNote($originalFilename);
            }
//*******************************************************************************************************


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_note_index');
        }

        return $this->render('admin/note/edit.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_note_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Note $note): Response
    {
        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($note);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_note_index');
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
