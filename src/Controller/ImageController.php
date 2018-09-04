<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends Controller
{
    /**
     * @Route("/", defaults={"page": "1"}, methods="GET|POST", name="image_index")
     * @Route("/page/{page}", requirements={"page"="\d+"}, methods={"GET"}, name="image_index_paginated")
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @param int $page
     * @return Response
     */
    public function index(Request $request, ImageRepository $imageRepository, int $page): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image, [
            'action' => $this->generateUrl('image_index'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // upload
            $file = $image->getFile();
            $upload = \Cloudinary\Uploader::upload($file->getRealPath(), [
                'type' => 'private',
                'folder' => 'cct/gallery/',
                'crop' => 'limit',
                'width' => 1000,
                'height' => 1000
            ]);
            $image->setFile($upload['public_id']);
            $image->setFormat($upload['format']);
            $image->setWidth($upload['width']);
            $image->setHeight($upload['height']);
            $image->setSize($upload['bytes']);

            // save
            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            $this->addFlash('success', 'Image uploaded!');

            return $this->redirectToRoute('image_index');
        }

        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findLatest($page),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/download/{id}", name="image_download", methods="GET")
     * @param Image $image
     * @return Response
     */
    public function download(Image $image): Response
    {
        $image->downloaded();
        $this->getDoctrine()->getManager()->flush();

        $opts = ['attachment' => true];
        $src = \Cloudinary::private_download_url($image->getFile(), $image->getFormat(), $opts);
        return $this->redirect($src);
    }

    /**
     * @Route("/show/{id}", name="image_show", methods="GET")
     * @param Request $request
     * @param Image $image
     * @return Response
     */
    public function show(Request $request, Image $image): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('image_index');
        }

        $image->viewed();
        $this->getDoctrine()->getManager()->flush();

        $src = cloudinary_url($image->getFile(), [
            'format' => $image->getFormat(),
            'type' => 'private',
            'width' => 600,
            'crop' => 'limit'
        ]);

        $title = htmlspecialchars($image->getTitle(), ENT_COMPAT | ENT_HTML5);
        return $this->json([
            'title' => ucfirst($title),
            'src' => $src,
        ]);
    }
}
