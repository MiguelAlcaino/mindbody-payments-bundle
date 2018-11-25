<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 *
 * @package MindBodyPaymentsBundle\Controller
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/index", name="admin_index")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('admin_transactions_index');
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/settings", name="admin_settings")
     */
    public function settingsAction(Request $request)
    {
        return $this->render('@MiguelAlcainoMindbodyPayments/Admin/settings.html.twig');
    }

    /**
     * @param Request         $request
     * @param FilesystemCache $cache
     *
     * @return Response
     * @Route("/settings/reset-cache", name="admin_settings_reset_cache")
     */
    public function resetCacheAction(Request $request, FilesystemCache $cache)
    {
        $cache->clear();

        $this->addFlash('notice', 'Todos los elementos cacheados han sido reseteados');

        return $this->redirectToRoute('admin_settings');
    }
}
