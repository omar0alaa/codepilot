<?php

namespace App\Services\Ai\Prompts;

/**
 * Prompt Template Builder
 * 
 * Constructs structured prompts for AI review analysis.
 * Each category has its own focused prompt for better results.
 */
class PromptTemplateBuilder
{
    /**
     * Build the complete review prompt with context and analysis instructions
     */
    public function buildReviewPrompt(string $diff, array $prContext): string
    {
        return $this->getSystemPrompt($prContext) . $this->getAnalysisPrompt($diff);
    }

    /**
     * Get system prompt establishing the AI reviewer persona and context
     */
    private function getSystemPrompt(array $context): string
    {
        $repoName = $context['repository']['full_name'] ?? 'unknown';
        $prNumber = $context['pr_number'] ?? 'unknown';
        $prTitle = $context['pr_title'] ?? 'unknown';
        $baseBranch = $context['base_branch'] ?? 'main';
        $headBranch = $context['head_branch'] ?? 'feature';

        return <<<PROMPT
You are CodePilot AI, an expert code reviewer with deep expertise in:
- Software security (OWASP Top 10, injection, XSS, CSRF)
- Performance optimization (N+1 queries, caching, indexing)
- SOLID principles and clean architecture
- Laravel/PHP best practices
- Code maintainability and readability
- Error handling and validation patterns

## Context
- Repository: {$repoName}
- Pull Request: #{$prNumber} - {$prTitle}
- Branch: {$headBranch} → {$baseBranch}

## Your Task
Analyze the following pull request diff and provide a comprehensive code review.

## Output Format
Respond with a **JSON object** matching this exact structure:
{
  "overall_score": <integer 0-100>,
  "category_scores": {
    "security": <integer 0-100>,
    "performance": <integer 0-100>,
    "maintainability": <integer 0-100>,
    "readability": <integer 0-100>,
    "architecture": <integer 0-100>
  },
  "issues": [
    {
      "file": "<filename>",
      "line": <line number or range as string>,
      "title": "<short title>",
      "severity": "<critical|warning|info>",
      "category": "<security|performance|maintainability|readability|architecture>",
      "description": "<detailed explanation of the problem>",
      "suggestion": "<specific code suggestion or fix>",
      "example_code": "<optional code example showing the fix>",
      "confidence": <float 0-1>
    }
  ],
  "summary": "<2-3 sentence review summary>",
  "suggestions": [
    {
      "type": "<improvement|optimization|refactor>",
      "description": "<general suggestion>",
      "priority": "<high|medium|low>"
    }
  ]
}

## Guidelines
- Be specific: always reference exact file names and line numbers when possible
- Be constructive: suggest fixes, don't just point out problems
- Be accurate: only flag real issues, not style preferences
- Severity levels: critical = security/data loss risk, warning = bug/performance, info = best practice
- Score guidelines: 90+ = excellent, 70-89 = good, 50-69 = needs work, <50 = poor
- Confidence: 1.0 = very certain, 0.7 = likely, 0.5 = uncertain

PROMPT;
    }

    /**
     * Get analysis prompt with the actual diff content
     */
    private function getAnalysisPrompt(string $diff): string
    {
        // Truncate large diffs to fit token limits
        $maxDiffLength = 12000;
        $truncated = strlen($diff) > $maxDiffLength;
        $diffContent = $truncated 
            ? substr($diff, 0, $maxDiffLength) . "\n\n... [Diff truncated, showing first {$maxDiffLength} characters]"
            : $diff;

        return <<<PROMPT
## Code to Review

```diff
{$diffContent}
```

Analyze the above code changes and provide your review as a JSON object. Focus on:
1. **Security**: SQL injection, XSS, CSRF, auth bypass, secret exposure
2. **Performance**: N+1 queries, missing indexes, unnecessary loops, caching
3. **Maintainability**: dead code, duplication, complex methods, tight coupling
4. **Readability**: naming, magic numbers, long methods, missing docs
5. **Architecture**: SOLID violations, layer separation, proper error handling

Respond ONLY with the JSON object. Do not include markdown or text before or after.

PROMPT;
    }

    /**
     * Get prompt for a focused single-category review
     */
    public function buildCategoryPrompt(string $category, string $diff): string
    {
        $categoryContext = match($category) {
            'security' => 'Focus on: SQL injection, XSS, CSRF, auth bypass, path traversal, secret leakage, unsafe deserialization',
            'performance' => 'Focus on: N+1 queries, missing indexes, O(n²) operations, unnecessary object creation, missing pagination',
            'maintainability' => 'Focus on: duplicate code, dead code, tight coupling, missing abstractions, god objects',
            'readability' => 'Focus on: naming conventions, magic numbers, long methods, deep nesting, missing comments',
            'architecture' => 'Focus on: SOLID violations, layer leaks, business logic in controllers/views, missing separation',
            default => 'General code review',
        };

        return <<<PROMPT
You are an expert code reviewer. {$categoryContext}

Analyze this diff and return a JSON object with:
{
  "issues": [
    {
      "file": "<filename>",
      "line": "<line number>",
      "title": "<issue title>",
      "severity": "<critical|warning|info>",
      "description": "<explanation>",
      "suggestion": "<fix>",
      "confidence": <0-1>
    }
  ],
  "score": <0-100 for this category>
}

Diff:
```diff
{$diff}
```

Respond ONLY with JSON.
PROMPT;
    }
}
