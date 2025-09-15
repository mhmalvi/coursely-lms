# MCP Server Installer Agent - Coursely LMS Project (2025 Edition)

This agent installs and configures essential MCP (Model Context Protocol) servers specifically for the Coursely LMS Laravel project development workflow in Claude Code CLI using the latest 2025 standards and official repositories.

**Project:** Coursely LMS (Laravel 7.x)  
**Location:** `C:\Users\d_rak\OneDrive\Desktop\codecanyon-VkTrkpsL-rocket-lms-learning-managem\Source\Source`  
**Focus:** Frontend Enhancement, UI/UX Modernization, and Full-Stack Development

## Essential MCP Servers for Coursely LMS (2025)

### Core Development & Version Control ✅ INSTALLED
- **GitHub MCP** - Repository management, PR workflows, issue tracking for project collaboration
- **Filesystem MCP** - Secure file operations for Laravel blade templates, SCSS files, and assets
- **Supabase MCP** - Database operations and backend service management (already configured)

### Frontend Development & Testing ✅ INSTALLED  
- **Playwright MCP** - Browser automation for testing UI enhancements and responsive design

### AI-Powered Development Tools ✅ INSTALLED
- **Sequential Thinking MCP** - Complex problem-solving for frontend architecture decisions
- **Memory MCP** - Knowledge persistence across development sessions
- **Context7 MCP** - Documentation management and context awareness for large codebase

### Laravel & PHP Development (Available)
- **Composer MCP** - PHP package management and dependency resolution
- **Laravel Artisan MCP** - Laravel command-line tool integration

### Additional Tools (Optional)
- **Search MCP** - Web research for UI/UX trends and best practices
- **Image Generation MCP** - Asset creation for modern UI components
- **Color Palette MCP** - Design system color management

## Installation Commands for Coursely LMS Project

### ✅ COMPLETED - Current Project Setup
```bash
# Core development tools (INSTALLED)
claude mcp add github --scope user -- npx -y @modelcontextprotocol/server-github
claude mcp add filesystem --scope user -- npx -y @modelcontextprotocol/server-filesystem
claude mcp add supabase --scope user -- npx -y @supabase/mcp-server-supabase@latest

# Frontend development & testing (INSTALLED)
claude mcp add playwright --scope user -- npx -y @executeautomation/playwright-mcp-server

# AI-powered development (INSTALLED)
claude mcp add context7 -- npx -y @upstash/context7-mcp@latest
claude mcp add sequential-thinking -- npx -y @modelcontextprotocol/server-sequential-thinking
claude mcp add memory -- npx -y @modelcontextprotocol/server-memory
```

### Optional Enhancements for Laravel Development
```bash
# Laravel-specific tools
claude mcp add composer -- npx -y @modelcontextprotocol/server-composer
claude mcp add laravel -- npx -y @modelcontextprotocol/server-laravel

# Enhanced search and research
claude mcp add search --scope user -e BRAVE_API_KEY=YOUR_KEY -- npx -y @modelcontextprotocol/server-brave-search
claude mcp add perplexity -- npx -y perplexity-mcp

# Image and design tools
claude mcp add image-gen -- npx -y @modelcontextprotocol/server-images
```

### Deployment Options for Coursely LMS

#### Render Deployment (Current Platform)
```bash
# Render MCP Server - NOTE: Currently not available as NPM package
# The official Render MCP is hosted at https://mcp.render.com/mcp
# Configuration requires manual setup in ~/.claude.json
# API Key configured: rnd_ffgurx6t9h9NIhBAzVCk5hylEBld

# For now, use Render API directly via REST calls or dashboard
# Deploy URL: https://coursely-lms.onrender.com
# Dashboard: https://dashboard.render.com/web/srv-d309qqt6ubrc73em7te0
```

#### Alternative Deployment Platforms
```bash
# Traditional PHP/Laravel hosting
claude mcp add digitalocean --scope user -e DIGITALOCEAN_API_TOKEN=YOUR_TOKEN -- npx -y @digitalocean/mcp
claude mcp add netlify --scope user -e NETLIFY_AUTH_TOKEN=YOUR_TOKEN -- npx -y @netlify/mcp

# Laravel-specific hosting
claude mcp add forge --scope user -e FORGE_API_TOKEN=YOUR_TOKEN -- npx -y @laravel/mcp-forge
claude mcp add vapor --scope user -e VAPOR_API_TOKEN=YOUR_TOKEN -- npx -y @laravel/mcp-vapor
```

### Search & Research Stack
```bash
# Multi-provider search (requires API keys)
claude mcp add omnisearch --scope user \
  -e TAVILY_API_KEY=YOUR_KEY \
  -e BRAVE_API_KEY=YOUR_KEY \
  -e KAGI_API_KEY=YOUR_KEY \
  -e PERPLEXITY_API_KEY=YOUR_KEY \
  -e JINA_AI_API_KEY=YOUR_KEY \
  -- npx -y mcp-omnisearch

# Brave Search (single provider)
claude mcp add search --scope user -e BRAVE_API_KEY=YOUR_KEY -- npx -y @modelcontextprotocol/server-brave-search
```

### Windows-Specific Commands
```bash
# On Windows (not WSL), wrap with cmd /c
claude mcp add github --scope user -- cmd /c npx -y @modelcontextprotocol/server-github
claude mcp add filesystem --scope user -- cmd /c npx -y @modelcontextprotocol/server-filesystem
```

## Configuration Files (2025 Format)

### User-Level Configuration (~/.claude.json)
```json
{
  "mcpServers": {
    "github": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-github"],
      "env": {
        "GITHUB_PERSONAL_ACCESS_TOKEN": "${GITHUB_TOKEN}"
      }
    },
    "filesystem": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-filesystem", "~/Documents", "~/Desktop"]
    },
    "perplexity": {
      "command": "npx",
      "args": ["-y", "perplexity-mcp"]
    }
  }
}
```

### Coursely LMS Project Configuration (.claude/settings.local.json)
```json
{
  "mcpServers": {
    "sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-sequential-thinking"]
    },
    "memory": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-memory"]
    },
    "context7": {
      "command": "npx",
      "args": ["-y", "@upstash/context7-mcp@latest"]
    }
  }
}
```

### Environment Variables for Coursely LMS (.env)
```env
# MCP Server Configuration
GITHUB_TOKEN=your_github_token_here
VERCEL_TOKEN=your_vercel_token_here
SUPABASE_URL=your_supabase_url_here
SUPABASE_ANON_KEY=your_supabase_anon_key_here

# Optional: Enhanced search capabilities
BRAVE_API_KEY=your_brave_search_key_here
PERPLEXITY_API_KEY=your_perplexity_key_here
```

## Configuration Scopes (2025)

### Scope Hierarchy
1. **Local-scoped** (default): Project-specific user settings, private to you
2. **User-scoped** (`--scope user`): Available across all your projects  
3. **Project-scoped** (`.mcp.json`): Shared team configurations, version-controlled

### Environment Variables Support
Claude Code supports environment variable expansion using `${VARIABLE_NAME}` syntax in configuration files.

## Management Commands

```bash
# List all installed MCP servers
claude mcp list

# Remove a server
claude mcp remove [server-name]

# Test a server connection
claude mcp get [server-name]

# Add with specific scope
claude mcp add [name] --scope [local|user|project] -- [command]
```

## Security & Best Practices (2025)

### API Key Management
- Store API keys as environment variables, never in configuration files
- Use `.env` files for local development
- Configure CI/CD secrets for production environments

### Windows Compatibility
- Native Windows requires `cmd /c` wrapper for npx commands
- WSL users can use standard Linux commands

### Token Management
- Default MCP output limit: 25,000 tokens
- Warning threshold: 10,000 tokens
- Configurable via `MAX_MCP_OUTPUT_TOKENS` environment variable

## Verification & Troubleshooting

```bash
# Verify Node.js installation
node --version

# Check Claude Code version
claude --version

# Restart Claude Code after configuration changes
# Then verify servers are loaded
claude mcp list
```

## MCP Tools Available for Coursely LMS Development

### ✅ Currently Installed & Active

**Core Development:**
- **mcp__github__*** - Repository management, version control, issue tracking
- **mcp__filesystem__*** - Secure file operations for Laravel/Blade/SCSS files
- **mcp__supabase__*** - Database operations and backend service management

**Frontend Development & Testing:**
- **mcp__playwright__*** - Browser automation, UI testing, responsive design validation

**AI-Powered Development:**
- **Sequential Thinking** - Complex problem solving for frontend architecture
- **Memory** - Knowledge persistence across development sessions
- **Context7** - Documentation awareness for large Laravel codebase

### Available for Future Enhancement

**Laravel-Specific:**
- **Composer** - PHP package management and dependency resolution
- **Laravel Artisan** - Command-line tool integration for migrations, seeders
- **Laravel Forge** - Server management and deployment automation
- **Laravel Vapor** - Serverless deployment for Laravel applications

**Design & Research:**
- **Search/Research** - UI/UX trend research and best practices
- **Image Generation** - Asset creation for modern components
- **Color Management** - Design system and theme management

## Registry & Discovery

- **Official Registry**: registry.modelcontextprotocol.io
- **Community Servers**: github.com/wong2/awesome-mcp-servers
- **Official Repository**: github.com/modelcontextprotocol/servers

## Project-Specific Usage Examples

### Frontend Enhancement Workflow
```bash
# 1. Test UI changes with Playwright
claude mcp playwright navigate https://localhost/coursely-lms
claude mcp playwright screenshot --name "before-enhancement"

# 2. Modify SCSS files using Filesystem MCP
claude mcp filesystem edit resources/sass/app.scss

# 3. Deploy to Vercel for testing
claude mcp vercel deploy --project coursely-lms-staging

# 4. Version control with GitHub MCP
claude mcp github create-pr --title "Modern UI Enhancement Phase 1"
```

### Development Best Practices for Coursely LMS
1. **Always use Filesystem MCP** for secure file operations on Laravel files
2. **Test UI changes** with Playwright before committing
3. **Use Sequential Thinking** for complex frontend architecture decisions
4. **Deploy to staging** with Vercel MCP for client preview
5. **Track progress** with Memory MCP across development sessions

## Current Installation Status ✅

**Successfully Installed (Verified):**
- ✅ GitHub MCP Server
- ✅ Filesystem MCP Server  
- ✅ Supabase MCP Server
- ✅ Playwright MCP Server
- ✅ Sequential Thinking MCP
- ✅ Memory MCP
- ✅ Context7 MCP

**Deployment Platform:**
- ⚠️ Render MCP Server (Not available as NPM package - use REST API or Dashboard)
- ✅ Render API Key Configured: `rnd_ffgurx6t9h9NIhBAzVCk5hylEBld`
- 🌐 Live URL: https://coursely-lms.onrender.com
- 📊 Dashboard: https://dashboard.render.com/web/srv-d309qqt6ubrc73em7te0

**Project Location:** `E:\Aethon_draft\lms\coursely-lms`

This MCP environment is now optimized specifically for Coursely LMS frontend enhancement and full-stack Laravel development with 2025's latest standards and best practices.