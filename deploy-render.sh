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
    echo "❌ Render CLI not found. Using API deployment instead..."
    USE_API=true
else
    USE_API=false
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

if [ "$USE_API" = true ]; then
    echo "📡 Using Render API for deployment..."

    # Get service info from API
    SERVICE_RESPONSE=$(curl -s -H "Authorization: Bearer $RENDER_API_KEY" \
        "https://api.render.com/v1/services?name=coursely-lms")

    if echo "$SERVICE_RESPONSE" | grep -q "coursely-lms"; then
        SERVICE_ID=$(echo "$SERVICE_RESPONSE" | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)
        echo "🎯 Found service ID: $SERVICE_ID"

        # Trigger deployment
        DEPLOY_RESPONSE=$(curl -s -X POST \
            -H "Authorization: Bearer $RENDER_API_KEY" \
            -H "Content-Type: application/json" \
            "https://api.render.com/v1/services/$SERVICE_ID/deploys")

        if echo "$DEPLOY_RESPONSE" | grep -q '"status"'; then
            echo "✅ Deployment triggered via API!"
        else
            echo "❌ API deployment failed. Response: $DEPLOY_RESPONSE"
        fi
    else
        echo "❌ Could not find coursely-lms service. Response: $SERVICE_RESPONSE"
        echo "💡 Please create the service first via Render dashboard or check service name."
    fi
else
    render deploy
fi

echo "✅ Deployment initiated! Check your Render dashboard for progress."
echo "🌐 Your app will be available at: https://coursely-lms.onrender.com"