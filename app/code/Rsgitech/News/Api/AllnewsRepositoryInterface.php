<?php
namespace Rsgitech\News\Api;

interface AllnewsRepositoryInterface
{
	public function save(\Rsgitech\News\Api\Data\AllnewsInterface $news);

    public function getById($newsId);

    public function delete(\Rsgitech\News\Api\Data\AllnewsInterface $news);

    public function deleteById($newsId);
}
