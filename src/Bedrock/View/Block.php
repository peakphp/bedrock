<?php

declare(strict_types=1);

namespace Peak\Bedrock\View;

use Peak\Bedrock\View;
use Peak\Common\Collection\Collection;
use Peak\Common\Interfaces\Renderable;
use Peak\Bedrock\View\Exceptions\BlockNotFoundException;

/**
 * Class Block
 * @package Peak\Bedrock\View
 */
class Block extends Collection implements Renderable
{
    /**
     * View instance
     * @var \Peak\Bedrock\View
     */
    public $view;

    /**
     * Block file in app views
     * @var string
     */
    public $block_file;
    
    /**
     * Block constructor.
     *
     * @param View $view
     * @param $block_file
     * @param $block_data
     * @throws BlockNotFoundException
     */
    public function __construct(View $view, $block_file, $block_data)
    {
        $this->view = $view;
        $this->block_file = $block_file;

        if (!file_exists($this->block_file)) {
            throw new BlockNotFoundException($block_file);
        }

        // stock block data
        parent::__construct($block_data);

        // lock the block data
        $this->readOnly();
    }

    /**
     * Render a block with vars
     *
     * @return string
     */
    public function render()
    {
        ob_start();
        include $this->block_file;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
