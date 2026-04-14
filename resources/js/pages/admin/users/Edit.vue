<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Facebook, Globe, Instagram, Linkedin, MessageCircleMore } from 'lucide-vue-next';
import { AtomButton as Button, AtomInput, AtomSelect } from '@/components/Atoms';

type UserDetail = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    socials?: Record<string, string> | null;
    avatar_url?: string | null;
    role: 'admin' | 'employee';
    is_active: boolean;
};

const props = defineProps<{
    user: UserDetail;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: '/admin/users',
            },
            {
                title: 'Edit',
                href: '/admin/users',
            },
        ],
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
    socials: {
        reddit: props.user.socials?.reddit ?? '',
        linkedin: props.user.socials?.linkedin ?? '',
        facebook: props.user.socials?.facebook ?? '',
        instagram: props.user.socials?.instagram ?? '',
        website: props.user.socials?.website ?? '',
    },
    avatar: null as File | null,
    remove_avatar: false,
    role: props.user.role,
    is_active: props.user.is_active,
    password: '',
    password_confirmation: '',
});

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(props.user.avatar_url ?? null);

type SocialKey = keyof typeof form.socials;
type SocialField = {
    key: SocialKey;
    label: string;
    icon: unknown;
    model: string;
    error: string | undefined;
};

const socialFields = computed<SocialField[]>(() => [
    {
        key: 'reddit' as SocialKey,
        label: 'Reddit',
        icon: MessageCircleMore,
        model: form.socials.reddit,
        error: form.errors['socials.reddit'],
    },
    {
        key: 'linkedin' as SocialKey,
        label: 'LinkedIn',
        icon: Linkedin,
        model: form.socials.linkedin,
        error: form.errors['socials.linkedin'],
    },
    {
        key: 'facebook' as SocialKey,
        label: 'Facebook',
        icon: Facebook,
        model: form.socials.facebook,
        error: form.errors['socials.facebook'],
    },
    {
        key: 'instagram' as SocialKey,
        label: 'Instagram',
        icon: Instagram,
        model: form.socials.instagram,
        error: form.errors['socials.instagram'],
    },
]);

const submit = (): void => {
    form.patch(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
        forceFormData: true,
    });
};

const onAvatarChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    form.avatar = file;
    form.remove_avatar = false;
    avatarPreview.value = URL.createObjectURL(file);
};

const removeAvatar = (): void => {
    form.avatar = null;
    form.remove_avatar = true;
    avatarPreview.value = null;

    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
};

const socialUrl = (value: string): string => value.trim();
</script>

<template>
    <Head :title="`Edit ${props.user.name}`" />

    <div class="page-shell py-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold">Edit user</h1>
            <p class="text-sm text-muted-foreground">Update user profile, role and optional password reset.</p>
        </div>

        <form class="section-card space-y-5" @submit.prevent="submit">
            <AtomInput
                id="name"
                v-model="form.name"
                name="name"
                label="Name"
                required
                maxlength="255"
                placeholder="Full name"
                :error="form.errors.name"
            />

            <AtomInput
                id="email"
                v-model="form.email"
                type="email"
                name="email"
                label="Email"
                required
                placeholder="user@example.com"
                :error="form.errors.email"
            />

            <AtomInput
                id="phone"
                v-model="form.phone"
                type="text"
                name="phone"
                label="Phone"
                placeholder="+39 333 1234567"
                :error="form.errors.phone"
            />

            <div class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="social in socialFields"
                    :key="social.key"
                    class="grid grid-cols-[1fr_auto] items-end gap-2"
                >
                    <AtomInput
                        :id="`social_${social.key}`"
                        v-model="form.socials[social.key]"
                        type="url"
                        :name="`socials[${social.key}]`"
                        :label="social.label"
                        :error="social.error"
                    />
                    <a
                        v-if="social.model"
                        :href="socialUrl(social.model)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mb-[2px] inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-muted-foreground transition-colors hover:text-foreground"
                        :aria-label="`Open ${social.label}`"
                    >
                        <component :is="social.icon" class="h-4 w-4" />
                    </a>
                    <span
                        v-else
                        class="mb-[2px] inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-muted-foreground"
                    >
                        -
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-[1fr_auto] items-end gap-2">
                <AtomInput
                    id="social_website"
                    v-model="form.socials.website"
                    type="url"
                    name="socials[website]"
                    label="Website"
                    :error="form.errors['socials.website']"
                />
                <a
                    v-if="form.socials.website"
                    :href="socialUrl(form.socials.website)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mb-[2px] inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="Open Website"
                >
                    <Globe class="h-4 w-4" />
                </a>
                <span
                    v-else
                    class="mb-[2px] inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-muted-foreground"
                >
                    -
                </span>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium">Avatar</label>
                <div class="flex flex-wrap items-center gap-3">
                    <img
                        v-if="avatarPreview"
                        :src="avatarPreview"
                        alt="Avatar preview"
                        class="h-14 w-14 rounded-full border border-gray-200 object-cover"
                    />
                    <input
                        id="avatar"
                        ref="avatarInput"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="block w-full max-w-sm text-sm file:mr-3 file:rounded-lg file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm"
                        @change="onAvatarChange"
                    />
                    <Button v-if="avatarPreview" type="button" variant="outline" size="sm" @click="removeAvatar">
                        Remove avatar
                    </Button>
                </div>
                <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
            </div>

            <AtomSelect
                id="role"
                v-model="form.role"
                name="role"
                label="Role"
                required
                :options="[
                    { value: 'employee', label: 'Employee' },
                    { value: 'admin', label: 'Admin' },
                ]"
                :error="form.errors.role"
            />

            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-[color:var(--color-light-gray)] px-4 py-3">
                <div class="space-y-0.5">
                    <p class="text-sm font-medium">Active account</p>
                    <p class="text-xs text-muted-foreground">Inactive users cannot access the platform.</p>
                </div>
                <input
                    id="is_active"
                    v-model="form.is_active"
                    name="is_active"
                    type="checkbox"
                    class="size-4 rounded border-gray-300"
                />
            </label>
            <p v-if="form.errors.is_active" class="text-sm text-destructive">{{ form.errors.is_active }}</p>

            <AtomInput
                id="password"
                v-model="form.password"
                type="password"
                name="password"
                label="New password (optional)"
                minlength="8"
                :error="form.errors.password"
            />

            <AtomInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                name="password_confirmation"
                label="Confirm new password"
                minlength="8"
                :error="form.errors.password_confirmation"
            />

            <div class="flex flex-wrap gap-2">
                <Button type="submit" :disabled="form.processing">Save changes</Button>
                <Button as-child variant="outline">
                    <Link href="/admin/users">Back to users</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
