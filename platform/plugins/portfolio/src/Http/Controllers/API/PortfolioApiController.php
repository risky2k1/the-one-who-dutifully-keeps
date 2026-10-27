<?php

namespace Botble\Portfolio\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Portfolio\Models\PortfolioCategory;
use Botble\Portfolio\Models\PortfolioExperience;
use Botble\Portfolio\Models\PortfolioProject;
use Botble\Portfolio\Models\PortfolioSkillGroup;
use Botble\Setting\Facades\Setting;
use Illuminate\Http\JsonResponse;

class PortfolioApiController extends BaseApiController
{
    /**
     * Get the full aggregated portfolio bundle for 3D single-viewport initialization.
     */
    public function index(): JsonResponse
    {
        $projects = PortfolioProject::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->with(['categories'])
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn ($p) => $this->formatProject($p));

        $skillCategories = PortfolioCategory::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->whereHas('skills')
            ->with(['skills' => fn ($q) => $q->orderBy('order', 'asc')])
            ->orderBy('order', 'asc')
            ->get();

        if ($skillCategories->isNotEmpty()) {
            $skillGroups = $skillCategories->map(fn ($g) => $this->formatCategorySkills($g));
        } else {
            $skillGroups = PortfolioSkillGroup::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->with(['skills' => fn ($q) => $q->orderBy('order', 'asc')])
                ->orderBy('order', 'asc')
                ->get()
                ->map(fn ($g) => $this->formatSkillGroup($g));
        }

        $experiences = PortfolioExperience::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn ($e) => $this->formatExperience($e));

        $persona = [
            'name' => Setting::get('portfolio_persona_name', 'Sora Lindqvist (Demo Persona)'),
            'role' => Setting::get('portfolio_persona_role', 'Staff Platform & Cloud Infrastructure Engineer'),
            'specialization' => Setting::get('portfolio_persona_specialization', 'Distributed Backend APIs • Kubernetes Fabric • GitOps CI/CD • OpenTelemetry'),
            'location' => Setting::get('portfolio_persona_location', 'Stockholm / Remote (Sample Profile)'),
            'availabilityBadge' => Setting::get('portfolio_persona_availability_badge', 'Demo Showcase • Interactive 3D World'),
            'introHeadline' => Setting::get('portfolio_intro_headline', 'Architecting calm, self-healing cloud platforms & low-latency API meshes.'),
            'introLead' => Setting::get('portfolio_intro_lead', 'Drive the coral studio coupe along the continuous pastel highway—either by selecting a destination in the top dock or clicking directly on any of the six sculptural landmarks in the 3D world.'),
            'introHighlights' => json_decode(Setting::get('portfolio_intro_highlights', '[]'), true) ?: [
                ['label' => 'Focus Domain', 'value' => 'Platform, Backend & DevOps'],
                ['label' => 'World Route', 'value' => '6 Interactive Sculptural Stops'],
                ['label' => 'Core Stack', 'value' => 'Go • Rust • K8s • Terraform'],
            ],
        ];

        $about = [
            'headline' => Setting::get('portfolio_about_headline', 'Infrastructure should feel invisible when it works—and unmistakable in its clarity when inspected.'),
            'narrative' => json_decode(Setting::get('portfolio_about_narrative', '[]'), true) ?: [
                'This demonstration profile represents a Staff Platform & Backend Engineer specializing in high-throughput API gateways, multi-region Kubernetes topologies, and deterministic GitOps delivery pipelines.',
                'Rather than treating DevOps as reactive ticket resolution, this practice treats infrastructure as a versioned software product: declarative, observable by default, and engineered with paved-road developer ergonomics.',
            ],
            'pillars' => json_decode(Setting::get('portfolio_about_pillars', '[]'), true) ?: [
                [
                    'title' => 'Deterministic Cloud Fabric',
                    'description' => 'Immutable infrastructure provisioned through modular Terraform and Crossplane control planes with automated drift remediation.',
                    'metric' => '100% Declarative IaC (Sample Metric)',
                ],
                [
                    'title' => 'Resilient Backend Services',
                    'description' => 'Memory-safe edge proxies and gRPC microservices in Rust and Go with adaptive concurrency limits and graceful degradation.',
                    'metric' => 'Sub-4ms P99 Gateway Overhead (Demo)',
                ],
                [
                    'title' => 'Deep System Observability',
                    'description' => 'Unified OpenTelemetry tracing, eBPF network telemetry, and SLO burn-rate alerting that surfaces root causes in seconds.',
                    'metric' => 'Zero-Blindspot Telemetry Mesh',
                ],
            ],
        ];

        $contact = [
            'headline' => Setting::get('portfolio_contact_headline', 'Ready to customize or connect?'),
            'subtitle' => Setting::get('portfolio_contact_subtitle', 'This interactive showcase is completely dynamic and backed by Botble CMS. You can modify every model, metric, destination coordinate, and persona attribute through the admin control plane.'),
            'availabilityNotice' => Setting::get('portfolio_contact_availability_notice', 'Accepting select staff platform consulting, technical advisory, and high-throughput systems design engagements.'),
            'socialProfiles' => json_decode(Setting::get('portfolio_contact_social_profiles', '[]'), true) ?: [
                [
                    'platform' => 'GitHub',
                    'handle' => '@sample-platform-engineer',
                    'configKey' => 'PORTFOLIO_DEMO_GITHUB_PROFILE',
                    'displayTarget' => 'github.com/sample-platform-engineer (Demo Placeholder)',
                    'badge' => 'Code Repositories',
                ],
                [
                    'platform' => 'LinkedIn',
                    'handle' => 'linkedin.com/in/sample-platform-architect',
                    'configKey' => 'PORTFOLIO_DEMO_LINKEDIN_PROFILE',
                    'displayTarget' => 'linkedin.com/in/sample-platform-architect (Demo Placeholder)',
                    'badge' => 'Professional Network',
                ],
            ],
            'directChannels' => json_decode(Setting::get('portfolio_contact_direct_channels', '[]'), true) ?: [
                [
                    'type' => 'Architecture Inquiries',
                    'label' => 'Direct Engineering Inquiry',
                    'displayValue' => 'contact@sample-platform-architect.local (Demo Inquiries)',
                    'configKey' => 'PORTFOLIO_DEMO_CONTACT_EMAIL',
                    'note' => 'PGP key fingerprint available on request for sensitive platform audits.',
                ],
            ],
            'actions' => json_decode(Setting::get('portfolio_contact_actions', '[]'), true) ?: [
                [
                    'label' => 'Download Architecture Profile (PDF Placeholder)',
                    'configKey' => 'PORTFOLIO_DEMO_RESUME_DOWNLOAD',
                    'actionType' => 'download',
                ],
                [
                    'label' => 'Return to Diorama Overlook',
                    'configKey' => 'PORTFOLIO_DEMO_RETURN_OVERVIEW',
                    'actionType' => 'return_intro',
                ],
            ],
        ];

        $destinations = json_decode(Setting::get('portfolio_destinations', '[]'), true) ?: [];

        $payload = [
            'isDemonstrationContent' => (bool) Setting::get('portfolio_is_demonstration_content', true),
            'demonstrationNotice' => Setting::get('portfolio_demonstration_notice', 'DEMONSTRATION PORTFOLIO: All profile details, metrics, organizations, and project endpoints are sample demonstration content for this interactive 3D architecture showcase.'),
            'persona' => $persona,
            'about' => $about,
            'destinations' => $destinations,
            'skillGroups' => $skillGroups,
            'projects' => $projects,
            'experiences' => $experiences,
            'contact' => $contact,
        ];

        return $this
            ->httpResponse()
            ->setData($payload)
            ->toApiResponse();
    }

    public function projects(): JsonResponse
    {
        $projects = PortfolioProject::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->with(['categories'])
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn ($p) => $this->formatProject($p));

        return $this
            ->httpResponse()
            ->setData($projects)
            ->toApiResponse();
    }

    public function project(string $slug): JsonResponse
    {
        $project = PortfolioProject::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->with(['categories'])
            ->where('slug', $slug)
            ->first();

        if (! $project) {
            return $this
                ->httpResponse()
                ->setError()
                ->setCode(404)
                ->setMessage('Project not found')
                ->toApiResponse();
        }

        return $this
            ->httpResponse()
            ->setData($this->formatProject($project))
            ->toApiResponse();
    }

    public function categories(): JsonResponse
    {
        $categories = PortfolioCategory::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->with([
                'children' => fn ($q) => $q->where('status', BaseStatusEnum::PUBLISHED)->orderBy('order', 'asc'),
                'projects' => fn ($q) => $q->where('status', BaseStatusEnum::PUBLISHED),
                'skills' => fn ($q) => $q->orderBy('order', 'asc'),
            ])
            ->orderBy('order', 'asc')
            ->get();

        return $this
            ->httpResponse()
            ->setData($categories)
            ->toApiResponse();
    }

    public function skills(): JsonResponse
    {
        $skillCategories = PortfolioCategory::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->whereHas('skills')
            ->with(['skills' => fn ($q) => $q->orderBy('order', 'asc')])
            ->orderBy('order', 'asc')
            ->get();

        if ($skillCategories->isNotEmpty()) {
            $groups = $skillCategories->map(fn ($g) => $this->formatCategorySkills($g));
        } else {
            $groups = PortfolioSkillGroup::query()
                ->where('status', BaseStatusEnum::PUBLISHED)
                ->with(['skills' => fn ($q) => $q->orderBy('order', 'asc')])
                ->orderBy('order', 'asc')
                ->get()
                ->map(fn ($g) => $this->formatSkillGroup($g));
        }

        return $this
            ->httpResponse()
            ->setData($groups)
            ->toApiResponse();
    }

    public function experiences(): JsonResponse
    {
        $experiences = PortfolioExperience::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn ($e) => $this->formatExperience($e));

        return $this
            ->httpResponse()
            ->setData($experiences)
            ->toApiResponse();
    }

    protected function formatProject(PortfolioProject $project): array
    {
        $categories = $project->categories;
        $primaryCategory = $categories->first()?->name ?? $project->category ?? 'Backend API';

        return [
            'id' => $project->slug,
            'title' => $project->name,
            'category' => $primaryCategory,
            'categories' => $categories->map(fn ($c) => [
                'id' => $c->slug,
                'name' => $c->name,
                'badge' => $c->badge,
                'accentColor' => $c->accent_color,
            ])->all(),
            'shortSummary' => $project->short_summary,
            'overview' => $project->overview,
            'role' => $project->role,
            'timeline' => $project->timeline,
            'accentColor' => $project->accent_color,
            'secondaryColor' => $project->secondary_color,
            'architectureDiagramType' => $project->architecture_diagram_type,
            'challenges' => $project->challenges ?: [],
            'outcomes' => $project->outcomes ?: [],
            'techStack' => $project->tech_stack ?: [],
            'metrics' => $project->metrics ?: [],
            'links' => $project->links ?: [],
        ];
    }

    protected function formatCategorySkills(PortfolioCategory $group): array
    {
        return [
            'id' => $group->slug,
            'title' => $group->name,
            'badge' => $group->badge ?: $group->name,
            'accentColor' => $group->accent_color ?: '#5ec2b7',
            'summary' => $group->description ?: '',
            'skills' => $group->skills->map(fn ($s) => [
                'name' => $s->name,
                'level' => $s->level,
                'context' => $s->context,
                'highlight' => (bool) $s->highlight,
            ])->all(),
        ];
    }

    protected function formatSkillGroup(PortfolioSkillGroup $group): array
    {
        return [
            'id' => $group->slug,
            'title' => $group->name,
            'badge' => $group->badge,
            'accentColor' => $group->accent_color,
            'summary' => $group->summary,
            'skills' => $group->skills->map(fn ($s) => [
                'name' => $s->name,
                'level' => $s->level,
                'context' => $s->context,
                'highlight' => (bool) $s->highlight,
            ])->all(),
        ];
    }

    protected function formatExperience(PortfolioExperience $exp): array
    {
        return [
            'id' => $exp->slug,
            'period' => $exp->period,
            'role' => $exp->role,
            'organization' => $exp->organization,
            'organizationNote' => $exp->organization_note,
            'location' => $exp->location,
            'summary' => $exp->summary,
            'highlights' => $exp->highlights ?: [],
            'technologies' => $exp->technologies ?: [],
        ];
    }
}
