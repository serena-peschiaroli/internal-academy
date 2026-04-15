<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import { AtomButton as Button, AtomInput } from '@/components/Atoms';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import { edit, update as updateProfile } from '@/routes/profile';
import { send } from '@/routes/verification';

type Socials = {
    reddit?: string | null;
    linkedin?: string | null;
    facebook?: string | null;
    instagram?: string | null;
    website?: string | null;
};

type ProfilePayload = {
    name: string;
    email: string;
    phone: string | null;
    socials: Socials;
    avatar: string | null;
    avatar_url: string | null;
};

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    profile: ProfilePayload;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const avatarInput = ref<HTMLInputElement | null>(null);
const temporaryPreviewUrl = ref<string | null>(null);
const avatarPreview = ref<string | null>(props.profile.avatar_url);

const form = useForm({
    name: props.profile.name ?? '',
    email: props.profile.email ?? '',
    phone: props.profile.phone ?? '',
    socials: {
        reddit: props.profile.socials?.reddit ?? '',
        linkedin: props.profile.socials?.linkedin ?? '',
        facebook: props.profile.socials?.facebook ?? '',
        instagram: props.profile.socials?.instagram ?? '',
        website: props.profile.socials?.website ?? '',
    },
    avatar: null as File | null,
    remove_avatar: false,
});

const onAvatarChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    if (temporaryPreviewUrl.value) {
        URL.revokeObjectURL(temporaryPreviewUrl.value);
    }

    form.avatar = file;
    form.remove_avatar = false;
    temporaryPreviewUrl.value = URL.createObjectURL(file);
    avatarPreview.value = temporaryPreviewUrl.value;
};

const removeAvatar = (): void => {
    if (temporaryPreviewUrl.value) {
        URL.revokeObjectURL(temporaryPreviewUrl.value);
        temporaryPreviewUrl.value = null;
    }

    form.avatar = null;
    form.remove_avatar = true;
    avatarPreview.value = null;

    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
};

const submit = (): void => {
    form.patch(updateProfile.url(), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            if (temporaryPreviewUrl.value) {
                URL.revokeObjectURL(temporaryPreviewUrl.value);
                temporaryPreviewUrl.value = null;
            }

            form.reset('avatar', 'remove_avatar');
            avatarPreview.value = (page.props.profile as ProfilePayload | undefined)?.avatar_url ?? null;
        },
    });
};

onBeforeUnmount(() => {
    if (temporaryPreviewUrl.value) {
        URL.revokeObjectURL(temporaryPreviewUrl.value);
    }
});
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="space-y-6">
        <div class="section-card space-y-6">
            <Heading
                variant="small"
                title="Profile information"
                description="Update your personal data, social links and avatar"
            />

            <form class="space-y-6" @submit.prevent="submit">
                <AtomInput
                    id="name"
                    v-model="form.name"
                    name="name"
                    label="Name"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                    :error="form.errors.name"
                />

                <AtomInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    name="email"
                    label="Email address"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                    :error="form.errors.email"
                />

                <AtomInput
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    name="phone"
                    label="Phone"
                    class="mt-1 block w-full"
                    placeholder="+39 333 1234567"
                    :error="form.errors.phone"
                />

                <div class="grid gap-4 md:grid-cols-2">
                    <AtomInput
                        id="social_reddit"
                        v-model="form.socials.reddit"
                        type="url"
                        name="socials[reddit]"
                        label="Reddit"
                        placeholder="https://reddit.com/u/username"
                        :error="form.errors['socials.reddit']"
                    />
                    <AtomInput
                        id="social_linkedin"
                        v-model="form.socials.linkedin"
                        type="url"
                        name="socials[linkedin]"
                        label="LinkedIn"
                        placeholder="https://linkedin.com/in/username"
                        :error="form.errors['socials.linkedin']"
                    />
                    <AtomInput
                        id="social_facebook"
                        v-model="form.socials.facebook"
                        type="url"
                        name="socials[facebook]"
                        label="Facebook"
                        placeholder="https://facebook.com/username"
                        :error="form.errors['socials.facebook']"
                    />
                    <AtomInput
                        id="social_instagram"
                        v-model="form.socials.instagram"
                        type="url"
                        name="socials[instagram]"
                        label="Instagram"
                        placeholder="https://instagram.com/username"
                        :error="form.errors['socials.instagram']"
                    />
                </div>

                <AtomInput
                    id="social_website"
                    v-model="form.socials.website"
                    type="url"
                    name="socials[website]"
                    label="Website"
                    placeholder="https://example.com"
                    :error="form.errors['socials.website']"
                />

                <div class="space-y-3">
                    <label for="avatar" class="text-sm font-medium text-foreground">Avatar</label>
                    <div class="flex flex-wrap items-center gap-4">
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            alt="Avatar preview"
                            class="h-14 w-14 rounded-full border border-gray-200 object-cover"
                        />
                        <div
                            v-else
                            class="flex h-14 w-14 items-center justify-center rounded-full border border-dashed border-gray-300 text-xs text-muted-foreground"
                        >
                            No avatar
                        </div>

                        <input
                            id="avatar"
                            ref="avatarInput"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="block w-full max-w-sm text-sm file:mr-3 file:rounded-lg file:border file:border-gray-300 file:bg-white file:px-3 file:py-2 file:text-sm"
                            @change="onAvatarChange"
                        />

                        <Button
                            v-if="avatarPreview"
                            type="button"
                            variant="outline"
                            @click="removeAvatar"
                        >
                            Remove avatar
                        </Button>
                    </div>
                    <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="-mt-4 text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link
                            :href="send()"
                            as="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            Click here to resend the verification email.
                        </Link>
                    </p>

                    <div
                        v-if="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-600"
                    >
                        A new verification link has been sent to your email address.
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing" data-test="update-profile-button">
                        Save
                    </Button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                            Saved.
                        </p>
                    </Transition>
                </div>
            </form>
        </div>
    </div>

    <DeleteUser />
</template>

