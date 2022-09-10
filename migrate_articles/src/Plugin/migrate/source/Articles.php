<?php
/**
 * @file
 * Contains \Drupal\migrate_articles\Plugin\migrate\source\Articles.
 */

namespace Drupal\migrate_articles\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "migrate_articles"
 * )
 */
class Articles extends Node
{
    /**
     * {@inheritdoc}
     */
    public function query()
    {
        // this queries the built-in metadata, but not the body, tags, or images.
        $query = parent::query();
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = $this->baseFields();
        $fields['body/format'] = $this->t('Format of body');
        $fields['body/value'] = $this->t('Full text of body');
        $fields['body/summary'] = $this->t('Summary of body');
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRow(Row $row)
    {
        $nid = $row->getSourceProperty('nid');

        // body (compound field with value, summary, and format)
        $result = $this->getDatabase()->query('
      SELECT
        fld.body_value,
        fld.body_summary,
        fld.body_format
      FROM
        {field_data_body} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
        foreach ($result as $record) {
            $row->setSourceProperty('body_value', $record->body_value);
            $row->setSourceProperty('body_summary', $record->body_summary);
            $row->setSourceProperty('body_format', $record->body_format);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        $ids['nid']['type'] = 'integer';
        $ids['nid']['alias'] = 'n';
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function bundleMigrationRequired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function entityTypeId()
    {
        return 'node';
    }

    /**
     * Returns the user base fields to be migrated.
     *
     * @return array
     *   Associative array having field name as key and description as value.
     */
    protected function baseFields()
    {
        $fields = array(
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Version ID'),
      'type' => $this->t('Type'),
      'title' => $this->t('Title'),
      'format' => $this->t('Format'),
      'teaser' => $this->t('Teaser'),
      'uid' => $this->t('Authored by (uid)'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'status' => $this->t('Published'),
      'sticky' => $this->t('Sticky at top of lists'),
      'language' => $this->t('Language (fr, en, ...)'),
    );
        return $fields;
    }
}
