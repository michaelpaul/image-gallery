<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends Controller
{
    /**
     * @Route("/", name="image_index", methods="GET|POST")
     */
    public function index(Request $request, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // upload
            $file = $image->getFile();
            $upload = \Cloudinary\Uploader::upload($file->getRealPath(), [
                'type'   => 'private',
                'folder' => 'cct/gallery/',
                'crop'   => 'limit',
                'width'  => 1000,
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
            'images' => $imageRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/download", name="image_download", methods="GET")
     */
    public function download(Image $image): Response
    {
        $image->downloaded();
        $this->getDoctrine()->getManager()->flush();

        $opts = [ 'attachment' => true ];
        $src = \Cloudinary::private_download_url($image->getFile(), $image->getFormat(), $opts);
        return $this->redirect($src);
    }

    /**
     * @Route("/{id}", name="image_show", methods="GET")
     */
    public function show(Image $image): Response
    {
        $image->viewed();
        $this->getDoctrine()->getManager()->flush();

        $src = cloudinary_url($image->getFile(), [
            'format' => $image->getFormat(),
            'type'   => 'private',
            'crop'   => 'thumb',
            'width'  => 400,
        ]);
        return $this->render('image/show.html.twig', ['image' => $image, 'src' => $src]);
    }
}
