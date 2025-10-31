# Silicon Flow Bundle

[English](README.md) | [中文](README.zh-CN.md)

此 Bundle 提供与 [SiliconFlow Chat Completions API](https://docs.siliconflow.cn/cn/api-reference/chat-completions/chat-completions) 的集成：

- 通过 `SiliconFlowApiClient` 封装 `POST /v1/chat/completions` 请求，支持 JSON 与 SSE 模式自动回退。
- `ChatCompletionService` 暴露面向业务的 `createCompletion` 方法，负责请求发送、结果持久化、错误记录。
- Doctrine 实体 `SiliconFlowConfig`、`SiliconFlowConversation`、`SiliconFlowImageGeneration`、`SiliconFlowModel` 与 `ChatCompletionLog` 分别用于管理多环境接入配置、对话/图片生成结果以及模型列表缓存，便于运营审计与回溯。
- 通过 EasyAdmin CRUD 控制器与 Symfony Console 命令实现结果可视化与离线重放（待二期扩展）。

## 目录结构

```
src/
  Client/            // 封装 SiliconFlow API 调用
  DTO/               // 请求与响应的轻量 DTO
  Entity/            // Doctrine 实体（贫血模型）
  Repository/        // Doctrine 查询
  Request/           // 构建 API 请求体的对象
  Response/          // 对应响应的解析器
  Service/           // 业务服务层
  DependencyInjection/ // Extension 与配置加载
  Resources/config/  // 服务定义
  SiliconFlowBundle.php
```

## 配置约定

```yaml
# config/packages/silicon_flow.yaml
tourze_silicon_flow:
  api_key: '%env(SILICON_FLOW_API_KEY)%'
  base_url: 'https://api.siliconflow.cn'
  request_timeout: 30
```

## 存储模型

- `SiliconFlowConfig`
  - `id`: 自增主键
  - `name`: 配置名称
  - `baseUrl`: API 基础地址
  - `apiToken`: 调用凭证
  - `isActive`: 是否启用
  - `priority`: 使用优先级（数值越大越优先）
  - `description`: 备注
  - `createdAt` / `updatedAt`: 创建与更新时间

- `SiliconFlowImageGeneration`
  - `id`: 自增主键
  - `model`: 使用模型
  - `prompt`/`negativePrompt`: 正向与反向提示
  - `imageSize`/`batchSize`/`seed`/`numInferenceSteps`: 关键请求参数
  - `imageUrls`: 返回图片地址数组
  - `inferenceTime`/`responseSeed`: 响应指标
  - `sender`: 关联 BizUser
  - `requestPayload`/`responsePayload`: 原始请求与响应
  - `createTime` / `updateTime`: 自动追踪时间

- `SiliconFlowConversation`
  - `id`: 自增主键
  - `conversationType`: `single|continuous`
  - `question`: 问题内容
  - `answer`: 返回答案
  - `sender`: 关联 BizUser
  - `contextId`: 上下文标识（持续对话使用）
  - `contextSnapshot`: 上下文快照（JSON 或文本）
  - `createTime` / `updateTime`: 自动追踪时间

- `SiliconFlowModel`
  - `id`: 自增主键
  - `modelId`: 模型标识（唯一）
  - `objectType`: OpenAPI 返回的对象类型
  - `modelCreatedAt`: 模型创建时间
  - `ownedBy`: 模型所属
  - `isActive`: 是否有效
  - `metadata`: 附加元数据
  - `createTime` / `updateTime`: 自动追踪时间

- `ChatCompletionLog`
  - `id`: 自增主键
  - `requestId`: SiliconFlow 返回的 `id`
  - `model`: 请求模型名
  - `requestPayload`: 原始请求体（JSON）
  - `responsePayload`: 原始响应体（JSON）
  - `status`: `success|failed`
  - `errorMessage`: 失败原因
  - `promptTokens` / `completionTokens` / `totalTokens`
  - `createdAt`: 请求发起时间

## 控制台命令

- `tourze:silicon-flow:sync-models`：同步 SiliconFlow 模型列表到本地数据库

## 验证流程

- 单元测试覆盖：
  - `ChatCompletionService` 发送请求、解析响应、写库。
  - SSE 返回场景的聚合（遇到 `stream=true` 时解析 `data:` 分块）。
- 质量门：`phpstan`、`phpunit` 针对 `packages/silicon-flow-bundle` 的限定执行。

## 下一步

- EasyAdmin CRUD 界面
- 失败请求的重放命令
- 多模型配额监控
