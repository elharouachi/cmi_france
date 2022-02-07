<?php

namespace App\Controller;

use App\Cmi\ArticleRetriever;
use App\Http\CmiApiRequester;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class ArticleController extends AbstractController
{

    /**
     * @var CmiApiRequester
     */
    private $articleRetriever;

    public function __construct(ArticleRetriever $articleRetriever)
    {
        $this->articleRetriever = $articleRetriever;
    }

    /**
     * @Route("/produits", name="cmi_list_article", methods={"GET"})
     */
    public function produitsAction(Request $request): Response
    {
        $articles = $this->articleRetriever->getArticles();

        return $this->render('produit/articles.html.twig', [
            'articles' => $articles,
        ]);

    }

    /**
     * @Route("/produits/{id}/detail/", name="cmi_detail_article", methods={"GET"})
     */
    public function detailAction(Request $request): Response
    {
        $article = $this->articleRetriever->getArticle($request->get('id'));

        return $this->render('produit/detail.html.twig', [
            'article' => $article,
        ]);

    }
}