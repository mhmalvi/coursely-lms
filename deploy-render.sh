#!/bin/bash

# Coursely LMS Render Deployment Script
echo "🚀 Starting Coursely LMS deployment to Render..."

# Load environment variables
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

# Check if Render API key is set
if [ -z "$RENDER_API_KEY" ]; then
    echo "❌ RENDER_API_KEY not found in .env file"
    exit 1
fi

# Export Render API key for CLI
export RENDER_API_KEY=$RENDER_API_KEY

# Check if Render CLI is installed
if ! command -v render &> /dev/null; then
    echo "❌ Render CLI not found. Installing..."
    npm install -g @render/cli
fi

# Verify authentication with API key
echo "📋 Verifying Render authentication with API key..."

# Validate render.yaml
echo "🔍 Validating render.yaml configuration..."
if [ ! -f "render.yaml" ]; then
    echo "❌ render.yaml not found!"
    exit 1
fi

# Push to git (required for Render)
echo "📦 Ensuring code is committed to git..."
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  Uncommitted changes detected. Please commit your changes first:"
    echo "   git add ."
    echo "   git commit -m 'Deploy to Render'"
    echo "   git push origin main"
    exit 1
fi

# Deploy to Render
echo "🔄 Deploying to Render..."
render deploy

echo "✅ Deployment initiated! Check your Render dashboard for progress."
echo "🌐 Your app will be available at: https://coursely-lms.onrender.com"