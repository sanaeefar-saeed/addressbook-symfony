<?php

declare(strict_types=1);
namespace AppBundle\Controller;

use AppBundle\Service\FileUploader;
use AppBundle\Entity\Contact;
use AppBundle\Form\ContactForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContactController
 * @package AppBundle\Controller
 */
class ContactController extends Controller
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function homeAction(): Response
    {

        $repository = $this->getDoctrine()->getRepository(Contact::class);
        $contacts = $repository->findAll();

        return $this->render('contact/home.html.twig', array(
            'contacts' => $contacts
        ));
    }

    /**
     * @Route("/contact/add", name="addContact")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return RedirectResponse|Response
     */
    public function addContact(Request $request, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ContactForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Contact $newContact */
            $newContact = $form->getData();

            /** @var UploadedFile $file */
            $file = $form['image']->getData();
            if ($file) {
                $fileName = $fileUploader->upload($file, $this->getParameter('contact_image_directory'));
                $newContact->setImage($fileName);
            }

            $em->persist($newContact);
            $em->flush();

            $this->addFlash('success', 'Contact added successfully!');
            return $this->redirectToRoute('home');

        }

        if ($form->isSubmitted() && $form->isValid() === false) {

            $this->addFlash('error', 'Please enter valid data');

        }

        return $this->render('contact/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("contact/edit/{id}", name="editContact")
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function editContact(Request $request, FileUploader $fileUploader): Response
    {

        $contactId = $request->attributes->get('id');

        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($contactId);

        if ($contact === null) {
            $this->addFlash('error', 'Contact Doesnt Exists!');
            return $this->redirectToRoute('home');
        }


        try {
            $contact->setImage(
                new File($this->getParameter('contact_image_directory') . '/' . $contact->getImage())
            );
        } catch (\Exception $exception) {
            $contact->setImage(
                new File($this->getParameter('contact_image_directory') . '/user.jpeg')
            );
        }


        $form = $this->createForm(ContactForm::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Contact $newContact */

            $newContact = $form->getData();
            /** @var UploadedFile $file */
            $file = $form['image']->getData();
            if ($file) {
                $fileName = $fileUploader->upload($file, $this->getParameter('contact_image_directory'));
                $newContact->setImage($fileName);
            }

            $this->addFlash('success', 'Contact Edited successfully!');
            $em->persist($newContact);
            $em->flush();
            return $this->redirectToRoute('home');

        } elseif ($form->isSubmitted() && $form->isValid() === false) {

            $this->addFlash('error', 'Please enter valid data');
        }

        return $this->render('contact/edit.html.twig', array(
            'contact' => $contact,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("contact/view/{id}", name="viewContact")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function viewContact(Request $request)
    {

        $contactId = $request->attributes->get('id');
        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($contactId);

        if ($contact === null) {
            $this->addFlash('error', 'Contact Doesnt Exists!');
            return $this->redirectToRoute('home');
        }

        $image_path = $contact->getImage();
        try {
            $contact->setImage(
                new File($this->getParameter('contact_image_directory') . '/' . $contact->getImage())
            );
        } catch (\Exception $exception) {
        }

        $form = $this->createForm(ContactForm::class, $contact);
        $form->handleRequest($request);

        return $this->render('contact/view.html.twig', array(
            'contact' => $contact,
            'image_path' => $image_path
        ));
    }

    /**
     * @Route("contact/del/{id}", name="deleteContact")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delContact(Request $request): RedirectResponse
    {

        $contactId = $request->attributes->get('id');
        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($contactId);

        if ($contact === null) {
            $this->addFlash('error', 'Contact Doesnt Exists!');
        } else {
            $this->getDoctrine()->getManager()
                ->remove($contact);
            $this->getDoctrine()->getManager()
                ->flush();
            $this->addFlash('success', 'Address deleted successfully!');
        }


        return $this->redirectToRoute('home');
    }

    /**
     * @Route("contact/search", name="deleteContact")
     * @param Request $request
     * @return Response
     */
    public function searchContact(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('AppBundle:Contact')->createQueryBuilder('contact');
        if ($request->query->getAlnum('q')) {
            $queryBuilder
                ->where('contact.firstName LIKE :search')
                ->orwhere('contact.lastName LIKE :search')
                ->orwhere('contact.email LIKE :search')
                ->orwhere('contact.phone LIKE :search')
                ->setParameter('search', '%' . $request->query->getAlnum('q') . '%');
        }
        $result = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('contact/home.html.twig', array(
            'contacts' => $result,
        ));
    }
}
