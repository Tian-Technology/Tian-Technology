# Tian-Tech 项目集合

## 项目概述

Tian-Tech 是一个包含多个子项目的综合性技术平台，涵盖了AI助手、聊天系统、办公工具、铁路信息等多个领域。本仓库包含了项目的前端页面、后端服务和相关资源文件。

## 项目结构

```
main/
├── LightningChat/          # 聊天系统
├── LightningChatPrivate/   # 私有聊天系统
├── WanQian/                # AI助手系统
├── home.zjjsw.com/         # 主页相关
├── key/                    # 密钥管理系统
├── login/                  # 登录系统
├── notice/                 # 通知系统
├── office/                 # 办公相关
├── program/                # 程序相关
├── railway/                # 铁路相关
├── text/                   # 文本相关
├── tianOS/                 # 操作系统相关
├── uploads/                # 上传文件
├── xtc/                    # 其他项目
├── 过年/                    # 节日相关
├── 重要内容备份/             # 备份文件
└── 各种HTML/PHP/Python文件   # 功能页面和服务端脚本
```

## 主要子项目

### 1. WanQian AI助手

**WanQian** 是基于智谱AI ChatGLM API开发的智能助手系统，提供聊天和图像生成功能。

- **主要文件**：
  - `WanQianchat.html` - 聊天界面
  - `WanQianimagine.html` - 图像生成界面
  - `WanQian.py` - Python版AI助手（本地运行）
  - `WanQiannewchat.js` - 聊天功能脚本
  - `WanQianChatimagine.js` - 图像生成功能脚本

- **特点**：
  - 支持多种模型选择
  - 气泡式聊天界面
  - 思考过程显示
  - 流式响应
  - 本地运行版本（Python）

### 2. LightningChat 聊天系统

**LightningChat** 是一个实时聊天系统，支持创建和加入聊天房间。

- **主要文件**：
  - `LightningChat.php` - 主聊天服务
  - `chat_api.php` - 聊天API
  - `LightningChatHall.html` - 聊天大厅
  - `LightningChatcreate.html` - 创建房间
  - `LightningChatjoin.html` - 加入房间

### 3. 登录系统

提供用户认证和个人信息管理功能。

- **主要文件**：
  - `login/index.html` - 登录界面
  - `login/register.html` - 注册界面
  - `login/auth.php` - 认证服务
  - `login/personal.html` - 个人中心

### 4. 铁路信息系统

提供铁路相关新闻和调图信息。

- **主要文件**：
  - `railway/index.html` - 铁路信息主页
  - `railway/train-time.html` - 列车时刻表
  - `railway/news/` - 铁路新闻

### 5. 办公工具

提供文档创建和查看功能。

- **主要文件**：
  - `office/index.html` - 办公工具主页
  - `office/create_word.html` - 创建文档
  - `office/view_word.html` - 查看文档

## 技术栈

- **前端**：HTML5, CSS3, JavaScript, Bootstrap
- **后端**：PHP, Python
- **API**：智谱AI ChatGLM API
- **存储**：JSON文件, 本地文件系统

## 快速开始

### 运行 WanQian AI助手 (Python版)

```bash
# 安装依赖
pip install requests

# 运行AI助手
python WanQian.py
```

### 访问前端页面

直接在浏览器中打开相应的HTML文件即可访问前端页面。例如：

- `index.html` - 主页
- `WanQian/WanQianchat.html` - WanQian聊天界面
- `LightningChat/LightningChatHall.html` - 聊天大厅

## 项目特点

1. **模块化设计**：各个功能模块相对独立，便于维护和扩展
2. **多平台支持**：包含Web版和本地运行版
3. **丰富的功能**：涵盖AI助手、聊天、办公等多个领域
4. **用户友好**：界面设计简洁美观，交互流畅

## 注意事项

- 部分功能需要后端服务支持，可能需要在本地搭建PHP环境
- AI助手功能需要有效的API密钥
- 部分功能可能需要网络连接才能正常使用

## 许可证

本项目为内部开发项目，仅供学习和参考使用。

## 联系方式

如有问题或建议，请联系项目维护人员。

---

© 2026 Tian-Tech. All rights reserved.
