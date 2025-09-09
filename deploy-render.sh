#!/bin/bash

# Coursely LMS Render Deployment Script
echo "🚀 Starting Coursely LMS deployment to Render..."

# Check if Render CLI is installed
if ! command -v render &> /dev/null; then
    echo "❌ Render CLI not found. Installing..."
    npm install -g @render/cli
fi

# Check if logged in to Render
echo "📋 Checking Render authentication..."
if ! render auth whoami &> /dev/null; then
    echo "🔑 Please log in to Render:"
    render auth login
fi

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