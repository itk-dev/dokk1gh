<?php

namespace ApiBundle\Controller;

use AppBundle\Service\TemplateManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;

/**
 * Class TemplateController
 * @package ApiBundle\Controller
 *
 * @Rest\View(serializerGroups={"api"})
 */
class TemplateController implements ClassResourceInterface
{
    private $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }

    /**
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *  response=200,
     *  description="List of templates",
     *  @SWG\Schema(
     *    type="array"
     *  )
     * )
     */
    public function cgetAction()
    {
        $result = $this->templateManager->getUserTemplates();

        return $result;
    }
}
