<?php

namespace App\Controller\Home;

use App\Entity\Admin\Messages;
use App\Entity\Note;
use App\Form\Admin\MessagesType;
use App\Repository\ImageRepository;
use App\Repository\NoteRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("", name="home_home")
     * @param SettingRepository $settingRepository
     * @return Response
     */
    public function index( SettingRepository $settingRepository, NoteRepository $noteRepository)
    {
        $setting=$settingRepository->findBy(['id'=>1]);
        $slider=$noteRepository->findBy([],[],4);
        $notes=$noteRepository->findAll();

        $newnotes=$noteRepository->findBy([],['id'=>'DESC'],4);
        return $this->render('home/home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'notes'=>$notes,
            'newnotes'=>$newnotes,
        ]);
    }


    /**
     * @Route("/note/{id}", name="note_show", methods={"GET"})
     */
    public function noteshow(Note $note,$id, ImageRepository $imageRepository): Response
    {

        $images=$imageRepository->findBy(['id'=>$id]);
        return $this->render('home/noteshow.html.twig', [
            'note' => $note,
            'images' => $images,
        ]);
    }

    /**
     * @Route("/aboutus", name="home_about")
     */
    public function homeabout(SettingRepository $settingRepository): Response
    {

        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function homecontact(SettingRepository $settingRepository,Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token');
        $setting=$settingRepository->findAll();
//dump($request);
//die();
        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)){
                $entityManager = $this->getDoctrine()->getManager();
                $message->setIp( $_SERVER['REMOTE_ADDR']);
                $message->setStatus('New');
                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success','Your message has been sent successfully ');

                //****************** SEND EMAIL*************************
                $email=(new Email()) ->from($setting[0]->getSmtpemail())

                    ->to($form['email']->getData())
                    ->subject("Don't Reply")
                    ->html("Dear" .$form['name']->getData()."<br>
                <p>We will evaluate your requests and contatct tou as soon as possible</p>
                Thank you<br>
                ===========================================================================
                <br>
                Address: ".$setting[0]->getAddress()."<br>
                Phone: " .$setting[0]->getPhone()."<br>"
                    ) ;
                $transport= new GmailTransport($setting[0]->getSmtpemail(), $setting[0]->getSmtppassword());
                $mailer= new Mailer($transport);
                $mailer->send($email);
                //**************************************************************************************
                return $this->redirectToRoute('home_contact');
            }
        }

        $setting=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'setting' => $setting,
        ]);
    }
    /**
     * @Route("/references", name="home_references")
     */
    public function homereferences(SettingRepository $settingRepository): Response
    {

        $setting=$settingRepository->findAll();
        return $this->render('home/references.html.twig', [
            'setting' => $setting,
        ]);
    }



}
