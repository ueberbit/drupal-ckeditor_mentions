<?php

namespace Drupal\ckeditor_mentions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Drupal\image\Entity\ImageStyle;

class CKMentionsController extends ControllerBase {

  public function getRealNameMatch($match = '', Request $request) {

    $message = ['result' => 'fail'];

    $str = trim(str_replace('@', '', $match));
    $str = strip_tags($str);

    if ($str) {

      $uid = \Drupal::currentUser()->id();

      $database = Database::getConnection('default');

      $query = $database->select('realname', 'rn');
      $query->leftJoin('users_field_data', 'ud', 'ud.uid = rn.uid');
      $query->leftJoin('user__user_picture', 'up', 'up.entity_id = rn.uid');
      $query->leftJoin('file_managed', 'fm', 'fm.fid = up.user_picture_target_id');
      $query->fields('rn', ['uid', 'realname']);
      $query->fields('fm', ['uri']);
      $query->condition('rn.realname', '%'.$query->escapeLike($str) . '%', 'LIKE');
      $query->isNotNull('rn.realname');
      $query->condition('ud.status', 1);

      // Exclude currently logged in user from returned list.
      if ($uid) {
        $query->condition('rn.uid', $uid, '!=');
      }

      $results = $query->execute();

      $matches = [];

      foreach($results as $result) {
        $url = '';

        if ($result->uri) {
          $url = ImageStyle::load('mentions_icon')->buildUrl($result->uri);
        }
        $matches[] = [
          'uid' => $result->uid,
          'name' => $result->realname,
          'image' => $url];
      }

      $message = ['result' => 'success', 'data' => $matches];

    }

    return new JsonResponse($message);

  }

}
