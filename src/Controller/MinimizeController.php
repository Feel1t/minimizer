<?php

namespace App\Controller;

use App\Entity\Link;
use App\Form\Type\LinkType;
use App\Hash\Generator;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MinimizeController extends AbstractController
{
    const LIST_LIMIT = 100;

    #[Route("/", name: 'main', methods: ['GET', 'HEAD'])]
    public function form(): Response
    {
        $link = new Link();

        $form = $this->createForm(LinkType::class, $link);

        return $this->renderForm('minimizer/form.html.twig', [
            'form'     => $form,
            'new_link' => null,
        ]);
    }

    #[Route("/list", name: 'links_list', methods: ['GET', 'HEAD'])]
    public function list(Request $request, LinkRepository $linkRepository): Response
    {
        $newLink = $request->get('hash');
        if ($newLink !== null) {
            $newLink = $this->generateUrl('link_redirect',['hash' => $newLink],UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $links = [];
        foreach ($linkRepository->findActiveLinks(self::LIST_LIMIT) as $link) {
            $links[] = [
                'link'        => $this->generateUrl('link_redirect', ['hash' => $link->getHash()], UrlGeneratorInterface::ABSOLUTE_URL),
                'long_url'    => $link->getLongUrl(),
                'click_count' => $link->getClickCount(),
            ];
        }

        return $this->renderForm('minimizer/list.html.twig', [
            'links'    => $links,
            'new_link' => $newLink,
        ]);
    }

    #[Route('/minimize', name: 'link_create', methods: ['POST'])]
    public function minimize(Request $request, LinkRepository $linkRepository, Generator $hashGenerator): Response
    {
        $link = new Link();

        $form = $this->createForm(LinkType::class, $link);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $linkData = $form->getData();

            try {
                $hash = $this->getHash($hashGenerator, $linkRepository);
            } catch (\Exception $exception) {
                return new Response($exception->getMessage());
            }

            $link->setHash($hash);
            $link->setLongUrl($linkData->getLongUrl());
            $link->setClickCount(0);
            $link->setDueDate($linkData->getDueDate());

            $linkRepository->add($link, true);
        }

        return $this->redirectToRoute('links_list', ['hash' => $link->getHash()]);
    }

    private function getHash(Generator $hashGenerator, LinkRepository $linkRepository, int $attempt = 0): string
    {
        if ($attempt >= 5) {
            throw new \Exception("Something went wrong. Try later.");
        }

        $hash = $hashGenerator->process($hashGenerator::BASE62_CHARS, 7);
        if ($linkRepository->findOneBy(['hash' => $hash]) !== null) {
            $attempt++;
            $hash = $this->getHash($hashGenerator, $linkRepository, $attempt);
        }

        return $hash;
    }

    #[Route('/r/{hash<^[a-zA-Z0-9]*$>}', name: 'link_redirect', methods: ['GET'])]
    public function redirectByLink(string $hash, LinkRepository $linkRepository): Response
    {
        $link = $linkRepository->getActiveLinkByHash($hash);
        if ($link === null) {
            throw $this->createNotFoundException('Page not found!');
        }

        $link->increaseClickCount();
        $linkRepository->add($link, true);

        return $this->redirect($link->getLongUrl());
    }
}