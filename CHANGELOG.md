# Changelog

All notable changes to the `Magiccart_Magicmenu` module are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-10-21

### Added

- Service contracts: `Api\MagicmenuRepositoryInterface`, `Api\Data\MagicmenuInterface` and
  `Api\Data\MagicmenuSearchResultsInterface` with a `Model\MagicmenuRepository` implementation.
- `Model\Config` as a dedicated scope configuration reader.
- Declarative database schema (`etc/db_schema.xml`) with `etc/db_schema_whitelist.json`.
- Idempotent data patch `Setup/Patch\Data\AddCategoryMagicAttributes` for the category attributes.
- `declare(strict_types=1)` and the copyright header in every PHP and template file.
- `ADMIN_RESOURCE` constant and `HttpGetActionInterface` / `HttpPostActionInterface` on every admin
  controller.
- Unit tests for the repository, the configuration reader and the model.

### Changed

- The `Registry` based transfer of the edited entity between the controller and the form block was
  replaced by the repository.
- CMS static blocks are resolved by identifier with `Magento\Cms\Block\BlockByIdentifier`; the
  deprecated `Magento\Cms\Block\Block` is no longer used.
- All frontend output is escaped with the `Magento\Framework\Escaper`.
- The frontend JavaScript no longer logs vendor branding and no longer depends on the missing
  `easing` RequireJS module.
- The file system access in the frontend block uses `Magento\Framework\Filesystem\Driver\File`
  instead of the native `file_exists()` function.
- The admin comment block that linked to an external product was replaced with a plain comment.

### Removed

- The `exclude_html_desktop` and `exclude_html_mobile` settings of the *Top Menu* and *Vertical
  Menu* groups, together with their default values and the `data-exclude_html_*` attributes they
  rendered. Nothing in the JavaScript or the LESS ever read these attributes.
- `Setup/InstallSchema.php` and `Setup/InstallData.php` (legacy install scripts).
- `Model/Category.php`, an unused model.
- `Block/Adminhtml/System/DynamicComment.php`, an external link in the admin.
- `etc/frontend/routes.xml`, there is no frontend controller.
- The unused `Block/Menu::getLogo()` method.

### Fixed

- The admin edit layouts (`magicmenu_menu_edit.xml`, `magicmenu_extra_edit.xml`) did not declare the
  `form` child block of the form container. The container calls `getChildBlock('form')` and threw
  `Call to a member function setData() on bool`, so the *Add New* / *Edit* pages were broken and the
  tab fields were rendered outside of the `edit_form` element. The unused
  `Block\Adminhtml\{Menu,Extra}\Edit\Form` blocks are now wired into the layouts.
- The `Block` grid renderer resolved CMS blocks by ID although identifiers are stored. Both the
  renderer and the frontend block now use the identifier.
- The admin delete action no longer tries to delete a record when no valid ID is provided.
- The accordion menu no longer passes an unsupported easing name to jQuery.
- The frontend JavaScript no longer depends on the `easing` RequireJS module, which does not exist
  in Magento and made the whole `magicmenu` module fail to load.
