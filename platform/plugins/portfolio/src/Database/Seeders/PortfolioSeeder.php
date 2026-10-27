<?php

namespace Botble\Portfolio\Database\Seeders;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Supports\BaseSeeder;
use Botble\Portfolio\Models\PortfolioCategory;
use Botble\Portfolio\Models\PortfolioExperience;
use Botble\Portfolio\Models\PortfolioProject;
use Botble\Portfolio\Models\PortfolioSkill;
use Botble\Portfolio\Models\PortfolioSkillGroup;
use Botble\Setting\Facades\Setting;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends BaseSeeder
{
    public function run(): void
    {
        PortfolioSkill::query()->delete();
        PortfolioSkillGroup::query()->delete();
        if (DB::getSchemaBuilder()->hasTable('portfolio_project_categories')) {
            DB::table('portfolio_project_categories')->delete();
        }
        PortfolioProject::query()->delete();
        PortfolioCategory::query()->delete();
        PortfolioExperience::query()->delete();

        // 1. Seed Hierarchical Categories
        // Parent Category 1
        $catPlatformArchitecture = PortfolioCategory::query()->create([
            'name' => 'Platform & Infrastructure Architecture',
            'slug' => 'platform-infrastructure-architecture',
            'parent_id' => 0,
            'badge' => 'Architecture',
            'accent_color' => '#5ec2b7',
            'description' => 'Multi-cluster orchestration, declarative cloud fabrics, eBPF networking, and high-cardinality observability meshes.',
            'order' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Child Category 1.1
        $catCloud = PortfolioCategory::query()->create([
            'name' => 'Cloud & Kubernetes Platform',
            'slug' => 'cloud-k8s',
            'parent_id' => $catPlatformArchitecture->id,
            'badge' => 'Infrastructure',
            'accent_color' => '#5ec2b7',
            'description' => 'Multi-cluster orchestration, eBPF networking, and declarative infrastructure provisioning.',
            'order' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Child Category 1.2
        $catObservability = PortfolioCategory::query()->create([
            'name' => 'Observability & Reliability',
            'slug' => 'observability-sre',
            'parent_id' => $catPlatformArchitecture->id,
            'badge' => 'SRE',
            'accent_color' => '#f29b8e',
            'description' => 'End-to-end distributed tracing, high-cardinality metrics, and automated chaos validation.',
            'order' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Parent Category 2
        $catSystemsEngineering = PortfolioCategory::query()->create([
            'name' => 'Distributed Systems & Engineering',
            'slug' => 'distributed-systems-engineering',
            'parent_id' => 0,
            'badge' => 'Engineering',
            'accent_color' => '#6ab7e8',
            'description' => 'High-concurrency backend services, low-latency API gateways, and progressive GitOps delivery pipelines.',
            'order' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Child Category 2.1
        $catBackend = PortfolioCategory::query()->create([
            'name' => 'Backend & Distributed Systems',
            'slug' => 'backend-api',
            'parent_id' => $catSystemsEngineering->id,
            'badge' => 'Services',
            'accent_color' => '#6ab7e8',
            'description' => 'High-concurrency control planes, API gateways, and event-driven state engines.',
            'order' => 0,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        // Child Category 2.2
        $catCicd = PortfolioCategory::query()->create([
            'name' => 'CI/CD & Release Automation',
            'slug' => 'cicd-gitops',
            'parent_id' => $catSystemsEngineering->id,
            'badge' => 'Delivery',
            'accent_color' => '#9b8ce8',
            'description' => 'Supply-chain security, ephemeral preview environments, and progressive canary rollouts.',
            'order' => 1,
            'status' => BaseStatusEnum::PUBLISHED,
        ]);

        foreach ([$catPlatformArchitecture, $catCloud, $catObservability, $catSystemsEngineering, $catBackend, $catCicd] as $cat) {
            SlugHelper::createSlug($cat);
        }

        // 2. Seed Projects
        $projects = [
            [
                'name' => 'AetherGate Distributed API Gateway & Rate-Limiting Mesh',
                'slug' => 'aethergate-api-mesh',
                'category' => 'Backend API',
                'short_summary' => 'High-throughput edge API gateway control plane in Go & Rust with distributed token-bucket rate limiting and dynamic Envoy xDS routing.',
                'overview' => 'AetherGate is a demonstration reference architecture for a multi-tenant API gateway designed to unify ingress routing, mTLS identity enforcement, and sub-millisecond quota coordination across regional microservice clusters without introducing a single point of failure.',
                'role' => 'Lead Systems Architect (Sample Demonstration Role)',
                'timeline' => 'Sample Architecture • 2025 Reference Design',
                'accent_color' => '#5ec2b7',
                'secondary_color' => '#a8d8ea',
                'architecture_diagram_type' => 'gateway_mesh',
                'challenges' => [
                    'Coordinating tenant rate limits across 24 stateless edge proxy nodes without incurring cross-AZ synchronous Redis round-trip penalties on every request.',
                    'Enabling zero-drop route table and TLS certificate hot-reloads via Envoy xDS streaming while maintaining strict schema validation.',
                    'Preserving end-to-end W3C TraceContext propagation across HTTP/2, gRPC, and asynchronous Kafka event boundaries.',
                ],
                'outcomes' => [
                    'Engineered a hybrid local-batch + asynchronous Redis CRDT token reservoir in Rust WASM, keeping median edge filter latency under 1.8 ms in benchmark simulations.',
                    'Built a declarative Go xDS control plane that validates OpenAPI/Protobuf route specs in CI before streaming atomic snapshot updates.',
                    'Integrated OpenTelemetry exemplars linking 99th-percentile latency spikes directly to trace spans in Grafana.',
                ],
                'tech_stack' => [
                    'Rust (WASM)',
                    'Go 1.22',
                    'Envoy Proxy (xDS)',
                    'gRPC / Protobuf',
                    'Redis Cluster',
                    'OpenTelemetry',
                ],
                'metrics' => [
                    ['label' => 'Simulated P99 Overhead', 'value' => '1.8 ms'],
                    ['label' => 'Control Plane Sync', 'value' => '< 250 ms'],
                    ['label' => 'Architecture Type', 'value' => 'Demo Reference'],
                ],
                'links' => [
                    [
                        'label' => 'Interactive Architecture Spec (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_AETHERGATE_SPEC',
                        'displayTarget' => 'demo://aethergate-architecture-spec (Configurable Placeholder)',
                        'kind' => 'architecture',
                    ],
                    [
                        'label' => 'Sample Repository Config (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_AETHERGATE_REPO',
                        'displayTarget' => 'repo://sample-platform/aethergate-mesh (Configurable Placeholder)',
                        'kind' => 'repository',
                    ],
                ],
                'order' => 0,
                'status' => BaseStatusEnum::PUBLISHED,
                'categories' => [$catBackend->id, $catSystemsEngineering->id],
            ],
            [
                'name' => 'Stratos Multi-Region Kubernetes Platform & Terraform Fabric',
                'slug' => 'stratos-cloud-fabric',
                'category' => 'Cloud Infrastructure',
                'short_summary' => 'Declarative multi-region Kubernetes platform utilizing Terraform modules, Cilium eBPF mesh, Karpenter node provisioning, and Crossplane.',
                'overview' => 'Stratos is a demonstration cloud platform blueprint showing how engineering organizations can provision isolated, compliance-ready Kubernetes environments across multiple regions with automated cost-aware compute scaling and eBPF L7 security boundaries.',
                'role' => 'Principal Platform Engineer (Sample Demonstration Role)',
                'timeline' => 'Sample Architecture • 2024–2025 Reference Design',
                'accent_color' => '#6ab7e8',
                'secondary_color' => '#c3bef0',
                'architecture_diagram_type' => 'multi_region_k8s',
                'challenges' => [
                    'Eliminating snowflake cluster drift across staging and multi-region production environments while allowing product teams self-service database and queue provisioning.',
                    'Replacing legacy iptables kube-proxy rules that degraded pod-to-pod network throughput at high service counts.',
                    'Right-sizing heterogeneous spot and on-demand compute pools automatically during diurnal traffic surges.',
                ],
                'outcomes' => [
                    'Composed modular Terraform foundations paired with Crossplane XRDs so application teams request Postgres, Redis, and S3 buckets via standard Kubernetes YAML manifests.',
                    'Adopted Cilium eBPF ClusterMesh for encrypted cross-region service discovery and identity-aware L7 network policies without sidecar memory overhead.',
                    'Configured Karpenter consolidation policies with SLO-aware PodDisruptionBudgets for rapid node bin-packing.',
                ],
                'tech_stack' => [
                    'Kubernetes (EKS)',
                    'Terraform / OpenTofu',
                    'Cilium eBPF',
                    'Crossplane',
                    'Karpenter',
                    'AWS (Multi-Region)',
                ],
                'metrics' => [
                    ['label' => 'Spot Cost Efficiency', 'value' => '42% Savings (Sim)'],
                    ['label' => 'Cross-Region Mesh', 'value' => 'eBPF WireGuard'],
                    ['label' => 'Architecture Type', 'value' => 'Demo Reference'],
                ],
                'links' => [
                    [
                        'label' => 'Topology Blueprint Visualizer (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_STRATOS_SPEC',
                        'displayTarget' => 'demo://stratos-multi-region-topology (Configurable Placeholder)',
                        'kind' => 'topology',
                    ],
                    [
                        'label' => 'Sample Terraform Modules (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_STRATOS_REPO',
                        'displayTarget' => 'repo://sample-platform/stratos-terraform-fabric (Configurable Placeholder)',
                        'kind' => 'repository',
                    ],
                ],
                'order' => 1,
                'status' => BaseStatusEnum::PUBLISHED,
                'categories' => [$catCloud->id, $catPlatformArchitecture->id],
            ],
            [
                'name' => 'PulseForge Ephemeral GitOps & Promotion Pipeline Engine',
                'slug' => 'pulseforge-gitops-pipeline',
                'category' => 'CI/CD Automation',
                'short_summary' => 'Hermetic container build engine and progressive canary promotion controller with automated SLO rollback verification.',
                'overview' => 'PulseForge demonstrates an enterprise continuous delivery pipeline that replaces brittle ad-hoc deployment scripts with cryptographically signed, SLSA-compliant container artifacts and automated Argo Rollouts canary promotions gated by metric queries.',
                'role' => 'Lead DevOps Architect (Sample Demonstration Role)',
                'timeline' => 'Sample Architecture • 2024 Reference Design',
                'accent_color' => '#9b8ce8',
                'secondary_color' => '#ffd3b6',
                'architecture_diagram_type' => 'gitops_pipeline',
                'challenges' => [
                    'Sluggish developer feedback loops caused by un-cached container builds across multi-architecture CPU target matrix.',
                    'Flaky release approvals relying on subjective human verification rather than objective Prometheus error-budget consumption.',
                    'Protecting cluster deployment credentials against supply-chain injection and compromised runner environments.',
                ],
                'outcomes' => [
                    'Configured BuildKit remote registry caching paired with multi-arch runners, shrinking median pull-request image build duration by 68%.',
                    'Designed Argo Rollouts canary templates that query Prometheus percentile latency and HTTP 5xx ratios, auto-aborting regressions within 90 seconds.',
                    'Integrated Sigstore Cosign keyless container signing with Kyverno admission enforcement policies.',
                ],
                'tech_stack' => [
                    'Argo Rollouts',
                    'GitHub Actions',
                    'Docker BuildKit',
                    'Sigstore Cosign',
                    'Kyverno',
                    'Prometheus Analysis',
                ],
                'metrics' => [
                    ['label' => 'Preview Spin-Up', 'value' => '< 90 sec (Demo)'],
                    ['label' => 'Canary Rollback', 'value' => 'Automated SLO'],
                    ['label' => 'Supply Chain', 'value' => 'SLSA / Cosign'],
                ],
                'links' => [
                    [
                        'label' => 'Pipeline Workflow Visualizer (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_PULSEFORGE_DEMO',
                        'displayTarget' => 'demo://pulseforge-gitops-visualizer (Configurable Placeholder)',
                        'kind' => 'demo',
                    ],
                    [
                        'label' => 'Sample GitOps Manifests (Demo Placeholder)',
                        'configKey' => 'PORTFOLIO_DEMO_PULSEFORGE_REPO',
                        'displayTarget' => 'repo://sample-platform/pulseforge-gitops (Configurable Placeholder)',
                        'kind' => 'repository',
                    ],
                ],
                'order' => 2,
                'status' => BaseStatusEnum::PUBLISHED,
                'categories' => [$catCicd->id, $catSystemsEngineering->id],
            ],
        ];

        foreach ($projects as $item) {
            $catIds = $item['categories'];
            unset($item['categories']);
            $project = PortfolioProject::query()->create($item);
            $project->categories()->sync($catIds);
            SlugHelper::createSlug($project);
        }

        // 3. Seed Skill Groups and Skills (associated with child categories)
        $skillGroups = [
            [
                'name' => 'Cloud & Kubernetes Platform',
                'slug' => 'cloud-k8s',
                'badge' => 'Infrastructure',
                'accent_color' => '#5ec2b7',
                'summary' => 'Multi-cluster orchestration, eBPF networking, and declarative infrastructure provisioning.',
                'order' => 0,
                'status' => BaseStatusEnum::PUBLISHED,
                'category_id' => $catCloud->id,
                'skills' => [
                    ['name' => 'Kubernetes (EKS / GKE)', 'level' => 'Architecture', 'context' => 'Multi-region federation, custom operators, Karpenter autoscaling', 'highlight' => true],
                    ['name' => 'Terraform & OpenTofu', 'level' => 'Architecture', 'context' => 'Composable infrastructure modules, policy-as-code with OPA/Rego', 'highlight' => true],
                    ['name' => 'Cilium & eBPF', 'level' => 'Production', 'context' => 'Kube-proxy replacement, L7 network policy, Hubble flow telemetry', 'highlight' => false],
                    ['name' => 'Crossplane', 'level' => 'Production', 'context' => 'Self-service cloud resource compositions via Kubernetes CRDs', 'highlight' => false],
                ],
            ],
            [
                'name' => 'Backend & Distributed Systems',
                'slug' => 'backend-api',
                'badge' => 'Services',
                'accent_color' => '#6ab7e8',
                'summary' => 'High-concurrency control planes, API gateways, and event-driven state engines.',
                'order' => 1,
                'status' => BaseStatusEnum::PUBLISHED,
                'category_id' => $catBackend->id,
                'skills' => [
                    ['name' => 'Go (Golang)', 'level' => 'Architecture', 'context' => 'Kubernetes controllers, gRPC microservices, distributed schedulers', 'highlight' => true],
                    ['name' => 'Rust', 'level' => 'Production', 'context' => 'Low-latency Envoy WASM filters, token-bucket rate limiters', 'highlight' => true],
                    ['name' => 'Envoy Proxy & gRPC', 'level' => 'Architecture', 'context' => 'xDS dynamic routing, mTLS service mesh, circuit breaking', 'highlight' => false],
                    ['name' => 'PostgreSQL & Redis Cluster', 'level' => 'Production', 'context' => 'Partitioned telemetry metadata, Lua atomic rate-limit scripts', 'highlight' => false],
                ],
            ],
            [
                'name' => 'CI/CD & Release Automation',
                'slug' => 'cicd-gitops',
                'badge' => 'Delivery',
                'accent_color' => '#9b8ce8',
                'summary' => 'Supply-chain security, ephemeral preview environments, and progressive canary rollouts.',
                'order' => 2,
                'status' => BaseStatusEnum::PUBLISHED,
                'category_id' => $catCicd->id,
                'skills' => [
                    ['name' => 'ArgoCD & Argo Rollouts', 'level' => 'Architecture', 'context' => 'Multi-cluster GitOps sync, metric-gated canary promotion', 'highlight' => true],
                    ['name' => 'GitHub Actions & Tekton', 'level' => 'Architecture', 'context' => 'Hermetic container builds, distributed remote caching', 'highlight' => true],
                    ['name' => 'Docker BuildKit & Nix', 'level' => 'Production', 'context' => 'Reproducible multi-arch distroless images, SBOM generation', 'highlight' => false],
                    ['name' => 'Sigstore Cosign & Kyverno', 'level' => 'Production', 'context' => 'Cryptographic image signing and admission verification', 'highlight' => false],
                ],
            ],
            [
                'name' => 'Observability & Reliability',
                'slug' => 'observability-sre',
                'badge' => 'SRE',
                'accent_color' => '#f29b8e',
                'summary' => 'End-to-end distributed tracing, high-cardinality metrics, and automated chaos validation.',
                'order' => 3,
                'status' => BaseStatusEnum::PUBLISHED,
                'category_id' => $catObservability->id,
                'skills' => [
                    ['name' => 'OpenTelemetry', 'level' => 'Architecture', 'context' => 'Unified collector pipelines, tail-based sampling, context propagation', 'highlight' => true],
                    ['name' => 'Prometheus & VictoriaMetrics', 'level' => 'Architecture', 'context' => 'High-cardinality time-series federation, SLO error budgets', 'highlight' => true],
                    ['name' => 'Grafana Tempo & Loki', 'level' => 'Production', 'context' => 'Exemplar-linked trace correlation and structured log indexing', 'highlight' => false],
                    ['name' => 'Chaos Mesh & Litmus', 'level' => 'Production', 'context' => 'Automated region failover drills and latency injection suites', 'highlight' => false],
                ],
            ],
        ];

        foreach ($skillGroups as $gData) {
            $skills = $gData['skills'];
            $categoryId = $gData['category_id'];
            unset($gData['skills'], $gData['category_id']);

            $group = PortfolioSkillGroup::query()->create($gData);
            SlugHelper::createSlug($group);
            foreach ($skills as $idx => $s) {
                $group->skills()->create(array_merge($s, [
                    'category_id' => $categoryId,
                    'order' => $idx,
                ]));
            }
        }

        // 4. Seed Experiences
        $experiences = [
            [
                'slug' => 'exp-staff-platform',
                'period' => '2023 — Present (Sample Timeline)',
                'role' => 'Staff Platform & Infrastructure Engineer',
                'organization' => 'NordicCloud Systems (Demonstration Organization)',
                'organization_note' => 'Sample Enterprise Platform Team',
                'location' => 'Stockholm / Hybrid (Demo)',
                'summary' => 'Leading internal developer platform architecture, multi-region Kubernetes reliability, and high-throughput API ingress engineering.',
                'highlights' => [
                    'Architected unified Envoy + Rust WASM edge gateway patterns and standardized OpenTelemetry instrumentation across core services.',
                    'Designed self-service Crossplane and Terraform compositions enabling product squads to provision compliant cloud resources autonomously.',
                ],
                'technologies' => ['Kubernetes', 'Go', 'Rust', 'Terraform', 'Cilium eBPF', 'OpenTelemetry'],
                'order' => 0,
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'slug' => 'exp-senior-devops',
                'period' => '2020 — 2023 (Sample Timeline)',
                'role' => 'Senior Site Reliability & DevOps Engineer',
                'organization' => 'Vespera FinTech Core (Demonstration Organization)',
                'organization_note' => 'Sample Distributed Ledger Infrastructure',
                'location' => 'Remote / EU (Demo)',
                'summary' => 'Owned CI/CD release automation, zero-downtime database migration workflows, and multi-AZ observability pipelines.',
                'highlights' => [
                    'Transitioned 40+ microservices to declarative ArgoCD GitOps workflows with automated Prometheus canary analysis.',
                    'Implemented hermetic container build pipelines with cryptographic image verification and automated chaos testing.',
                ],
                'technologies' => ['ArgoCD', 'GitHub Actions', 'AWS EKS', 'Prometheus', 'PostgreSQL', 'Grafana'],
                'order' => 1,
                'status' => BaseStatusEnum::PUBLISHED,
            ],
            [
                'slug' => 'exp-backend-eng',
                'period' => '2018 — 2020 (Sample Timeline)',
                'role' => 'Distributed Systems Backend Engineer',
                'organization' => 'Kestrel Telemetry Labs (Demonstration Organization)',
                'organization_note' => 'Sample Streaming Data Platform',
                'location' => 'Gothenburg (Demo)',
                'summary' => 'Built high-concurrency gRPC ingestion services, stream processing workers, and low-latency caching layers.',
                'highlights' => [
                    'Developed Go and gRPC telemetry collectors with bounded backpressure queues and partitioned time-series storage.',
                    'Collaborated with infrastructure teams to containerize stateful workloads and automate cluster monitoring.',
                ],
                'technologies' => ['Go', 'gRPC', 'Redis Cluster', 'Kafka', 'Docker', 'Linux'],
                'order' => 2,
                'status' => BaseStatusEnum::PUBLISHED,
            ],
        ];

        foreach ($experiences as $exp) {
            PortfolioExperience::query()->create($exp);
        }

        // 5. Save Settings
        Setting::set([
            'portfolio_persona_name' => 'Sora Lindqvist (Demo Persona)',
            'portfolio_persona_role' => 'Staff Platform & Cloud Infrastructure Engineer',
            'portfolio_persona_specialization' => 'Distributed Backend APIs • Kubernetes Fabric • GitOps CI/CD • OpenTelemetry',
            'portfolio_persona_location' => 'Stockholm / Remote (Sample Profile)',
            'portfolio_persona_availability_badge' => 'Demo Showcase • Interactive 3D World',
            'portfolio_intro_headline' => 'Architecting calm, self-healing cloud platforms & low-latency API meshes.',
            'portfolio_intro_lead' => 'Drive the coral studio coupe along the continuous pastel highway—either by selecting a destination in the top dock or clicking directly on any of the six sculptural landmarks in the 3D world.',
            'portfolio_intro_highlights' => json_encode([
                ['label' => 'Focus Domain', 'value' => 'Platform, Backend & DevOps'],
                ['label' => 'World Route', 'value' => '6 Interactive Sculptural Stops'],
                ['label' => 'Core Stack', 'value' => 'Go • Rust • K8s • Terraform'],
            ]),
            'portfolio_demonstration_notice' => 'DEMONSTRATION PORTFOLIO: All profile details, metrics, organizations, and project endpoints are sample demonstration content for this interactive 3D architecture showcase.',
            'portfolio_is_demonstration_content' => '1',
            'portfolio_about_headline' => 'Infrastructure should feel invisible when it works—and unmistakable in its clarity when inspected.',
            'portfolio_about_narrative' => json_encode([
                'This demonstration profile represents a Staff Platform & Backend Engineer specializing in high-throughput API gateways, multi-region Kubernetes topologies, and deterministic GitOps delivery pipelines.',
                'Rather than treating DevOps as reactive ticket resolution, this practice treats infrastructure as a versioned software product: declarative, observable by default, and engineered with paved-road developer ergonomics.',
            ]),
            'portfolio_about_pillars' => json_encode([
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
            ]),
            'portfolio_contact_headline' => 'Ready to customize or connect?',
            'portfolio_contact_subtitle' => 'All endpoints below are clearly marked demonstration placeholders configured in the CMS or you can send a message directly using the contact form.',
            'portfolio_contact_availability_note' => 'Demonstration Mode Active — Contact messages are stored directly in Botble CMS.',
            'portfolio_contact_channels' => json_encode([
                [
                    'id' => 'email-placeholder',
                    'label' => 'Engineering Contact (Configurable Placeholder)',
                    'value' => 'platform.architect@example.invalid',
                    'note' => 'Safe RFC 2606 placeholder — click to copy sample address',
                    'actionType' => 'copy',
                ],
                [
                    'id' => 'git-placeholder',
                    'label' => 'Git & Architecture Blueprints (Placeholder)',
                    'value' => 'repo://the-one-who-rules-them-all',
                    'note' => 'Main repository and polyglot architecture blueprints',
                    'actionType' => 'copy',
                ],
                [
                    'id' => 'explore-projects',
                    'label' => 'Inspect Architecture Case Studies',
                    'value' => 'Navigate to 04 Projects Landmark',
                    'note' => 'Drive the car to the Isometric Citadel to inspect detailed specs',
                    'actionType' => 'jump_projects',
                ],
                [
                    'id' => 'return-intro',
                    'label' => 'Return Car to Welcome Gate',
                    'value' => 'Navigate back to 01 Intro Overview',
                    'note' => 'Drives along the continuous road back to the wide diorama view',
                    'actionType' => 'return_intro',
                ],
            ]),
        ])->save();
    }
}
