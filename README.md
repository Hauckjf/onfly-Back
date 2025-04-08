# 🚀 Onfly

Sistema Full Stack para gestão de pedidos de viagens corporativas. Desenvolvido como parte de um desafio técnico, o projeto conta com uma API REST robusta em Laravel e uma interface moderna construída com Vue.js + Vite + TypeScript.

---

## 📚 Sumário

- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Como Executar o Projeto](#como-executar-o-projeto)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Rodando os Testes](#rodando-os-testes)
- [Screenshots](#screenshots)
- [Considerações Finais](#considerações-finais)

---

## 📖 Visão Geral

O Sistema permite que usuários corporativos criem, visualizem e gerenciem pedidos de viagem. A aplicação possui autenticação via JWT, controle de permissões por papel, user ou admin,
 notificação de status e interface com filtros interativos, oferecendo uma experiência completa e fluida.

Estrategias e Planejamento:
https://excalidraw.com/#json=2YGb3MrifGU_hrZqjGMoM,ZFDaZhOT7-aPK8ITHj3DcA
---

## ✅ Funcionalidades

### 🔒 Autenticação (JWT)
- Login e registro com tokens protegidos via Laravel Sanctum
- Rotas protegidas com controle de acesso (usuário e admin)

### 📝 Gestão de Viagens
- Criação de pedidos de viagem (com destino, datas e status)
- Listagem com filtros por status, período e destino
- Visualização de pedido individual
- Atualização de status para “aprovado” ou “cancelado” (restrita a admins)
- Regras de negócio: impossibilidade de cancelar pedidos já aprovados caso não for um admin
- Cada usuário pode ver, criar e editar apenas seus próprios pedidos

### 🔔 Notificações
- Envio de notificação visual ao usuário em caso de aprovação ou cancelamento de pedidos

### 🧪 Testes
- Testes unitários e de feature cobrindo autenticação e fluxo de pedidos

---

## 🛠️ Tecnologias Utilizadas

### Backend
- **Laravel 10**
- **Sanctum** (Autenticação JWT)
- **MySQL**
- **PHPUnit**
- **Docker** (PHP + NGINX + MySQL)
- **Spatie Laravel Permission** (controle de papéis e permissões)

### Frontend
- **Vue 3** + **Vite**
- **TypeScript**
- **Tailwind CSS**
- **Pinia** (state management)
- **Axios**
- **Universal-Cookie** (gerenciamento de cookies)
- **Toastify-js** (notificações visuais)

---

## ▶️ Como Executar o Projeto

### Pré-requisitos
- Docker + Docker Compose
- Node.js e npm instalados

### Clonando o Repositório

```bash Front
git clone https://github.com/Hauckjf/onfly-Front.git
cd onfly-Front
npm install
docker-compose up -d


```bash Backend
git clone https://github.com/Hauckjf/onfly-Back.git
cd onfly-Back
docker-compose exec app bash
composer install
php artisan migrate:refresh --seed
exit
docker-compose up -d
