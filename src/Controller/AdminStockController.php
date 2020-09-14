<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Stock;
use App\Form\ArticleQtyType;
use App\Form\ArticleType;
use App\Form\StockType;
use App\Repository\ArticleRepository;
use App\Repository\BelongingRepository;
use App\Repository\StockRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminStockController extends AbstractController
{

    /**
     * @var BelongingRepository
     */
    private $belongingRepository;
    /**
     * @var StockRepository
     */
    private $stockRepository;
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(BelongingRepository $belongingRepository, StockRepository $stockRepository, ArticleRepository $articleRepository, ObjectManager $manager)
    {

        $this->belongingRepository = $belongingRepository;
        $this->stockRepository = $stockRepository;
        $this->manager = $manager;
        $this->articleRepository = $articleRepository;
    }

    /**
     * @Route("/admin", name="admin.stock.index")
     * @return Response
     */
    public function index(): Response
    {
        $stocks = $this->stockRepository->findAll();

        return $this->render('admin/stock/index.html.twig', [
            'controller_name' => 'AdminStockController',
            'stocks' => $stocks
        ]);
    }


    /**
     * @Route("/admin/stock/create", name="admin.stock.new")
     * @param Request $request
     * @return Response
     */
    public function newStock(Request $request): Response
    {
        $stock = new Stock();
        $formStock = $this->createForm(StockType::class, $stock);
        $formStock->handleRequest($request);

        if ($formStock->isSubmitted() && $formStock->isValid()){
            $this->manager->persist($stock);
            $this->manager->flush();

            return $this->redirectToRoute('admin.stock.index');
        }

        return $this->render('admin/stock/new.html.twig', [
            'article' => $stock,
            'formStock' => $formStock->createView()
        ]);
    }


    /**
     * @Route("admin/stock/{id}/add", name="admin.stock.inspect.addArticle")
     * @param Stock $stock
     * @param $id
     * @return Response
     */
    public function addArticle($id, Stock $stock): Response
    {
        $articles = $this->articleRepository->findAllArticleNotInCurrentStock($id);

        return $this->render('admin/stock/add.html.twig', [
            'articles' => $articles,
            'stock' => $stock,
        ]);
    }



    /**
     * @Route("/admin/stock/{id}", name="admin.stock.inspect")
     * @param Stock $stock
     * @param $id
     * @return Response
     */
    public function inspectStock(Stock $stock, $id): Response
    {
        //j'ai besoin de récupérer l'état du stock qui correspond à l'id du stock donnée
        $belongs = $this->belongingRepository->findBy(array('stock' => $id));

        return $this->render('admin/stock/inspect.html.twig', [
            'stock' => $stock,
            'belongs' => $belongs
        ]);
    }



    /**
     * @Route("/admin/stock/article/create", name="admin.stock.article.new")
     * @param Request $request
     * @return Response
     */
    public function newArticle(Request $request): Response
    {
        $article = new Article();
        $formArticle = $this->createForm(ArticleType::class, $article);
        $formArticle->handleRequest($request);

        if ($formArticle->isSubmitted() && $formArticle->isValid()){
            $this->manager->persist($article);
            $this->manager->flush();

            return $this->redirectToRoute('admin.stock.index');
        }

        return $this->render('admin/stock/article/new.html.twig', [
            'article' => $article,
            'formArticle' => $formArticle->createView()
        ]);
    }



    /**
     * @Route("/admin/stock/{id}/{id_belong}", name="admin.stock.article.edit" )
     * @param Stock $stock
     * @param $id
     * @param $id_belong
     * @param Request $request
     * @return Response
     */
    public function editArticle(Stock $stock, $id, $id_belong, Request $request): Response
    {
        $belong = $this->belongingRepository->find($id_belong);

        $formQty = $this->createForm(ArticleQtyType::class, $belong);
        $formQty->handleRequest($request);

        if ($formQty->isSubmitted() && $formQty->isValid()){
            $this->manager->flush();
            return $this->redirectToRoute('admin.stock.inspect', array('id' => $id));
        }


        return $this->render('admin/stock/article/edit.html.twig', [
            'stock' => $stock,
            'belong' => $belong,
            'formQty' => $formQty->createView()
        ]);
    }






}

















