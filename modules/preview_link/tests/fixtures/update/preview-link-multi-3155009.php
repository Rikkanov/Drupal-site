<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Contains database additions to drupal-8.bare.standard.php.gz for testing the
 * upgrade path of https://www.drupal.org/project/preview_link/issues/3155009.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
$connection->update('config')
  ->fields([
    'data' => serialize(array_merge_recursive($extensions, ['module' => [
      'preview_link' => 0,
    ]])),
  ])
  ->condition('name', 'core.extension')
  ->execute();

// Update modules lists.
$connection->insert('key_value')
  ->fields(array(
    'collection',
    'name',
    'value',
  ))
  ->values(array(
    'collection' => 'system.schema',
    'name' => 'preview_link',
    'value' => 's:4:"8101";',
  ))
  ->values(array(
    'collection' => 'entity.definitions.installed',
    'name' => 'preview_link.entity_type',
    'value' => 'O:36:"Drupal\Core\Entity\ContentEntityType":40:{s:25:" * revision_metadata_keys";a:1:{s:16:"revision_default";s:16:"revision_default";}s:15:" * static_cache";b:1;s:15:" * render_cache";b:1;s:19:" * persistent_cache";b:1;s:14:" * entity_keys";a:9:{s:2:"id";s:2:"id";s:5:"token";s:5:"token";s:9:"entity_id";s:9:"entity_id";s:14:"entity_type_id";s:14:"entity_type_id";s:8:"revision";s:0:"";s:6:"bundle";s:0:"";s:8:"langcode";s:0:"";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";}s:5:" * id";s:12:"preview_link";s:16:" * originalClass";s:38:"Drupal\preview_link\Entity\PreviewLink";s:11:" * handlers";a:4:{s:7:"storage";s:38:"Drupal\preview_link\PreviewLinkStorage";s:4:"form";a:1:{s:12:"preview_link";s:40:"Drupal\preview_link\Form\PreviewLinkForm";}s:6:"access";s:45:"Drupal\Core\Entity\EntityAccessControlHandler";s:12:"view_builder";s:36:"Drupal\Core\Entity\EntityViewBuilder";}s:19:" * admin_permission";N;s:25:" * permission_granularity";s:11:"entity_type";s:8:" * links";a:0:{}s:21:" * bundle_entity_type";N;s:12:" * bundle_of";N;s:15:" * bundle_label";N;s:13:" * base_table";s:12:"preview_link";s:22:" * revision_data_table";N;s:17:" * revision_table";N;s:13:" * data_table";N;s:11:" * internal";b:0;s:15:" * translatable";b:0;s:19:" * show_revision_ui";b:0;s:8:" * label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:12:"Preview Link";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:19:" * label_collection";s:0:"";s:17:" * label_singular";s:0:"";s:15:" * label_plural";s:0:"";s:14:" * label_count";a:0:{}s:15:" * uri_callback";N;s:8:" * group";s:7:"content";s:14:" * group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:7:"Content";s:12:" * arguments";a:0:{}s:10:" * options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:" * field_ui_base_route";N;s:26:" * common_reference_target";b:0;s:22:" * list_cache_contexts";a:0:{}s:18:" * list_cache_tags";a:1:{i:0;s:17:"preview_link_list";}s:14:" * constraints";a:1:{s:26:"EntityUntranslatableFields";N;}s:13:" * additional";a:0:{}s:8:" * class";s:38:"Drupal\preview_link\Entity\PreviewLink";s:11:" * provider";s:12:"preview_link";s:14:" * _serviceIds";a:0:{}s:18:" * _entityStorages";a:0:{}s:20:" * stringTranslation";N;}',
  ))
  ->values(array(
    'collection' => 'entity.definitions.installed',
    'name' => 'preview_link.field_storage_definitions',
    'value' => 'a:5:{s:2:"id";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:" * type";s:7:"integer";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:4:"size";s:6:"normal";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:" * fieldDefinition";r:2;s:13:" * definition";a:2:{s:4:"type";s:18:"field_item:integer";s:8:"settings";a:6:{s:8:"unsigned";b:1;s:4:"size";s:6:"normal";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"prefix";s:0:"";s:6:"suffix";s:0:"";}}}s:13:" * definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:2:"ID";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:9:"read-only";b:1;s:8:"provider";s:12:"preview_link";s:10:"field_name";s:2:"id";s:11:"entity_type";s:12:"preview_link";s:6:"bundle";N;s:13:"initial_value";N;}}s:5:"token";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:" * type";s:6:"string";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:" * fieldDefinition";r:36;s:13:" * definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}}s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:13:"Preview Token";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:62:"A token that allows any user to view a preview of this entity.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"preview_link";s:10:"field_name";s:5:"token";s:11:"entity_type";s:12:"preview_link";s:6:"bundle";N;s:13:"initial_value";N;}}s:9:"entity_id";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:" * type";s:6:"string";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:" * fieldDefinition";r:71;s:13:" * definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}}s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:9:"Entity Id";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:13:"The entity Id";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"preview_link";s:10:"field_name";s:9:"entity_id";s:11:"entity_type";s:12:"preview_link";s:6:"bundle";N;s:13:"initial_value";N;}}s:14:"entity_type_id";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:" * type";s:6:"string";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:" * fieldDefinition";r:106;s:13:" * definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}}s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:14:"Entity Type Id";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:18:"The entity type Id";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"preview_link";s:10:"field_name";s:14:"entity_type_id";s:11:"entity_type";s:12:"preview_link";s:6:"bundle";N;s:13:"initial_value";N;}}s:19:"generated_timestamp";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:7:" * type";s:9:"timestamp";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:1:{s:4:"type";s:3:"int";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:18:" * fieldDefinition";r:141;s:13:" * definition";a:2:{s:4:"type";s:20:"field_item:timestamp";s:8:"settings";a:0:{}}}s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:19:"Generated Timestamp";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:31:"The time the link was generated";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"required";b:1;s:8:"provider";s:12:"preview_link";s:10:"field_name";s:19:"generated_timestamp";s:11:"entity_type";s:12:"preview_link";s:6:"bundle";N;s:13:"initial_value";N;}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.entity_schema_data',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:11:"primary key";a:1:{i:0;s:2:"id";}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.field_schema_data.entity_id',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:6:"fields";a:1:{s:9:"entity_id";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;s:8:"not null";b:1;}}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.field_schema_data.entity_type_id',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:6:"fields";a:1:{s:14:"entity_type_id";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;s:8:"not null";b:1;}}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.field_schema_data.generated_timestamp',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:6:"fields";a:1:{s:19:"generated_timestamp";a:2:{s:4:"type";s:3:"int";s:8:"not null";b:0;}}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.field_schema_data.id',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:6:"fields";a:1:{s:2:"id";a:4:{s:4:"type";s:6:"serial";s:8:"unsigned";b:1;s:4:"size";s:6:"normal";s:8:"not null";b:1;}}}}',
  ))
  ->values(array(
    'collection' => 'entity.storage_schema.sql',
    'name' => 'preview_link.field_schema_data.token',
    'value' => 'a:1:{s:12:"preview_link";a:1:{s:6:"fields";a:1:{s:5:"token";a:4:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;s:8:"not null";b:1;}}}}',
  ))
  ->execute();

$connection->insert('config')
  ->fields(array(
    'collection',
    'name',
    'data',
  ))
  ->values(array(
    'collection' => '',
    'name' => 'preview_link.settings',
    'data' => 'a:2:{s:20:"enabled_entity_types";a:0:{}s:5:"_core";a:1:{s:19:"default_config_hash";s:43:"op5zJk4bmDxEGOgAYNCkpAQOLG0YZhNMrxlSkdYSQIM";}}',
  ))
  ->execute();

$connection->schema()->createTable('preview_link', array(
  'fields' => array(
    'id' => array(
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ),
    'token' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ),
    'entity_id' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ),
    'entity_type_id' => array(
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ),
    'generated_timestamp' => array(
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ),
  ),
  'primary key' => array(
    'id',
  ),
  'mysql_character_set' => 'utf8mb4',
));

$connection->insert('preview_link')
  ->fields(array(
    'id',
    'token',
    'entity_id',
    'entity_type_id',
    'generated_timestamp',
  ))
  ->values(array(
    'id' => '1',
    'token' => 'de3a19ee-1edc-4b2e-9af8-f512dddcddcc',
    'entity_id' => '2',
    'entity_type_id' => 'entity_test_mulrevpub',
    'generated_timestamp' => '1594297787',
  ))
  ->execute();
