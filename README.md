<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Store Locator

简体中文 | [English](docs/README.en.md)

适用于 **PHP 8.4+、Laravel 13、Filament 5** 的门店管理与公开查询包，依赖 CMS Core 和 Qrcode。

## 功能与边界

| 能力 | 说明 |
| --- | --- |
| 门店管理 | 新增、编辑、删除、发布状态、权重排序 |
| 地理信息 | 国家、省市、地址、经纬度、邮编、地铁线路 |
| 联系方式与营业时间 | 网站、电话、邮箱、每周时间、例外日期及说明 |
| 门店媒体 | Logo 和多张 Banner，使用 Filament 原生 FileUpload |
| 公开 API | 已发布门店列表和详情、分页、距离排序 |
| CMS 集成 | 模块自登记、权限目录、中英文资源及租户数据范围 |

本包不提供地图服务、地址地理编码或“当前是否营业”的自动判断，也不包含旧 Nova 的自定义字段管理界面。后台部分表单标题仍使用英文，不代表界面已经完整本地化。

## 1. 安装

先在宿主 Composer 配置本私有包及所需 `fayne/*` 依赖的软件源：

```bash
composer require fayne/laravel-filament-store-locator
php artisan vendor:publish --tag=store-locator-config
php artisan cms:install
```

Laravel 自动发现服务提供者并加载迁移。`cms:install` 运行已注册迁移、初始化权限目录并发布 Filament 静态资源；生产自动部署使用 `--force --no-interaction`。已有 Core 项目也可运行 `php artisan migrate` 加载本包迁移，再重跑 Core 安装补齐权限。不要在已有业务数据的数据库上执行 `migrate:fresh`。

媒体磁盘默认使用 `public`，须确保其 URL 可访问，并按宿主配置创建存储链接。无需为了本包的上传控件额外安装文件选择器。

## 2. 接入后台与权限

本包通过自身 ServiceProvider 向 Core 的 ModuleRegistry 登记 `store-locator`。继承 Core AdminPanelProvider / TenantPanelProvider 的项目自动接入：单租户在普通后台管理，多租户在 workspace 管理。

完全自定义的 PanelProvider 在配置 CMS、middleware、guard、租户及显式插件后调用：

```php
return app(\Fayne\CMS\Support\Modules\ModuleRegistry::class)->applyTo($panel);
```

如需手动接入，可配置 `Fayne\StoreLocator\Filament\StoreLocatorPlugin::make()`；同 ID 的显式插件会被保留，不要在自动接入之后重复注册。

权限定义在 `resources/permissions.php`，名称和分组翻译位于 `resources/lang/{zh_CN,en}`。安装不自动给已有角色增加授权；必须在对应范围分配门店权限。多租户账号还须具备有效成员资格，平台超级管理员不自动绕过租户归属。

## 3. 配置

发布后的文件是 **`config/store-locator.php`**：

```php
return [
    'disk' => env('STORE_LOCATOR_DISK', 'public'),
    'routes' => [
        'enabled' => env('STORE_LOCATOR_ROUTE_ENABLED', true),
        'cached' => env('STORE_LOCATOR_ROUTE_CACHED', true),
        'max_age' => (int) env('STORE_LOCATOR_ROUTE_MAX_AGE', 3600),
    ],
];
```

`enabled` 控制公开路由，`cached` 控制列表服务端缓存，`max_age` 控制 HTTP Cache-Control。列表缓存使用 cache tags，启用时必须选择支持标签的缓存驱动；不支持时设 `STORE_LOCATOR_ROUTE_CACHED=false`。列表服务端缓存使用 rememberForever，不会按 max_age 自动到期；模型新增、更新、删除及后台排序会触发缓存失效。公开响应具有缓存头，多租户部署的代理/CDN 必须正确区分主机与请求 URL。

配置变更后按部署流程刷新配置/路由缓存，并重新加载长驻进程。

## 4. 公开 API

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | `/modules/store-locator/stores` | 已发布门店分页列表 |
| GET | `/modules/store-locator/stores/{store}` | 已发布门店详情；未发布返回 404 |

列表默认每页 15 条，`page_size` 支持 1–100。距离排序示例：

```text
GET /modules/store-locator/stores?order_by=distance&latitude=31.23&longitude=121.47&page_size=20
```

距离排序必须同时提交合法经纬度；纬度范围 -90–90，经度范围 -180–180。该查询依赖数据库三角函数，需在目标数据库验收。API 使用 Laravel Resource 输出，媒体由磁盘/路径转换，不能将上传的内部数据结构直接当作公开 URL。

单租户使用本站范围 0；多租户由 ResolvePublicTenant 根据请求主机解析租户，需要配置有效租户域名。后台语言与门店数据不是同一个概念，本包不提供门店内容翻译版本管理。

## 5. 程序使用

```php
use Fayne\StoreLocator\Models\Store;

$stores = Store::query()->published()->orderBy('weight')->get();
```

模型自动限定当前站点/租户。多租户命令和队列中须显式设置 Core TenantContext；不能通过脱离上下文的查询跨租户读取。Logo 和 Banner 使用包内媒体 casts，营业时间、品牌等使用结构化属性；自定义写入入口仍须负责业务校验与授权。

## 6. 测试与上线

在本包目录执行：

```bash
composer install
composer test
composer format
composer analyse
```

上线至少验证：安装迁移、角色授权、CRUD 与排序、媒体上传和公开 URL、发布状态过滤、缓存失效、目标数据库距离排序；多租户另验域名解析及跨租户隔离。自动化测试不能代替真实文件存储、CDN 和浏览器验收。

## 合作与贡献

通过 Issue 描述需求或缺陷，附版本、复现步骤及预期行为。合并请求须包含相关测试和中英文文档更新，并说明未验证的环境；不要提交凭据、业务数据或 `composer.lock`。商业合作及授权请联系 [Fayne Guo](mailto:fayne.guo@gmail.com)。

## 许可证

采用[专有许可证](LICENSE.md)，使用和分发须遵守授权限制；第三方依赖遵循各自许可证。
