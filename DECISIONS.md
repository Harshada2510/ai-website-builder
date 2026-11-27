
# Project Decisions

## AI Website Builder

This document explains the major technical decisions, implementation choices, features excluded from the current scope, and possible future improvements.

## 1. Key Technical Choices

### Laravel

**Decision:** Laravel was selected as the backend framework.

**Reason:**
- Provides structured MVC architecture.
- Simplifies routing, controllers, validation and database operations.
- Provides built-in file storage and migration support.
- Makes application development and maintenance easier.

### MySQL / TiDB Cloud

**Decision:** MySQL-compatible database technology was used.

**Reason:**
- Stores business information and generated website data.
- Supports structured relational data.
- TiDB Cloud provides a managed database for deployment.

### Bootstrap

**Decision:** Bootstrap was used for frontend styling.

**Reason:**
- Responsive design components.
- Faster UI development.
- Consistent layouts across different screen sizes.
- Reduces the need to build common UI components from scratch.

### Gemini AI

**Decision:** Gemini AI was integrated through its OpenAI-compatible API endpoint.

**Reason:**
- Generates website content using business information.
- Supports AI-assisted website creation.
- Allows individual website sections to be regenerated.
- Helps reduce manual website development effort.

### Render

**Decision:** Render was selected for application deployment.

**Reason:**
- Supports Docker-based deployment.
- Provides a hosted application URL.
- Supports environment variables for production configuration.
- Allows the application to connect to an external managed database.

## 2. Important Implementation Decisions

### Business Information as the Starting Point

Website generation begins with business information instead of relying only on a generic prompt.

This helps generate content that is relevant to the business.

### Source-Based Content

The application accepts business links and uploaded files as information sources.

The intention is to use available source information rather than inventing business details.

When information is missing or conflicting, it should not be presented as verified fact.

### Editable AI Output

Generated websites are not treated as final, unchangeable output.

Users can edit content, regenerate individual sections, customize appearance and manage section visibility.

### Public Website Publishing

Generated websites can be published and accessed using public business-specific URLs.

## 3. Features Cut from the Current Scope

The following features were not included in the current implementation scope:

- Complete user authentication and role-based access management.
- Custom domain purchasing and DNS configuration.
- Advanced drag-and-drop visual website editing.
- Website visitor analytics and reporting.
- Payment gateway and subscription management.
- Multiple AI provider integrations.
- Automated SEO auditing.

These features may be considered in future development.

## 4. Challenges and Solutions

### AI API Integration

**Challenge:** Integrating an external AI service and handling API response delays or failures.

**Approach:** Integrated Gemini through its OpenAI-compatible API endpoint and configured an AI request timeout.

### Database Deployment

**Challenge:** Connecting the deployed Laravel application to a managed cloud database.

**Approach:** Configured TiDB Cloud connectivity, database environment variables and TLS certificate handling.

### File Upload Management

**Challenge:** Accepting business documents and images safely.

**Approach:** Implemented file type and size validation and used Laravel storage.

### Website Publishing

**Challenge:** Making generated websites accessible through public URLs.

**Approach:** Implemented publishing functionality and business-specific website slugs.

## 5. What's Next?

Potential improvements include:

1. Add authentication and authorization.
2. Introduce more website templates.
3. Add custom domain support.
4. Improve source verification and conflict handling.
5. Add website analytics.
6. Improve mobile editing experience.
7. Add more AI providers and fallback options.
8. Add automated SEO recommendations.
9. Improve background processing for longer AI requests.

## 6. Conclusion

The project demonstrates a full-stack AI-powered website generation workflow using Laravel, MySQL-compatible database technology, Bootstrap and Gemini AI.

The application supports business information management, source uploads, AI website generation, editing, customization and publishing.

The implementation focuses on making website creation easier for businesses while keeping generated content editable.
