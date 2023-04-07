import React, {useEffect} from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/Auth/InputError';
import InputLabel from '@/Components/Auth/InputLabel';
import TextInput from '@/Components/Auth/TextInput';
import { Head, Link, useForm } from '@inertiajs/inertia-react';

export default function Register() {
  const { data, setData, post, errors, reset } = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  useEffect(() => {
    return () => {
      reset('password', 'password_confirmation');
    };
  }, []);

  const onHandleChange = (event) => {
    setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
  };

  const submit = (e) => {
    e.preventDefault();

    post(route('register'));
  };

  return (
    <GuestLayout>
      <Head title="Register" />

      <form onSubmit={submit}>
        <div className="mb-3">
          <InputLabel forInput="name" value="Name" />

          <TextInput
            type="text"
            name="name"
            value={data.name}
            autoComplete="name"
            isFocused={true}
            handleChange={onHandleChange}
            required
          />

          <InputError message={errors.name} className="mt-2" />
        </div>

        <div className="mb-3">
          <InputLabel forInput="email" value="Email" />

          <TextInput
            type="email"
            name="email"
            value={data.email}
            autoComplete="username"
            handleChange={onHandleChange}
            required
          />

          <InputError message={errors.email} className="mt-2" />
        </div>

        <div className="mb-3">
          <InputLabel forInput="password" value="Password" />

          <TextInput
            type="password"
            name="password"
            value={data.password}
            autoComplete="new-password"
            handleChange={onHandleChange}
            required
          />

          <InputError message={errors.password} className="mt-2" />
        </div>

        <div className="mb-3">
          <InputLabel forInput="password_confirmation" value="Confirm Password" />

          <TextInput
            type="password"
            name="password_confirmation"
            value={data.password_confirmation}
            handleChange={onHandleChange}
            required
          />

          <InputError message={errors.password_confirmation} className="mt-2" />
        </div>

        <div className="mb-0">
          <div className="d-flex justify-content-end align-items-baseline">
            <Link href={route('login')} className="text-muted me-3">
              Already registered?
            </Link>

            <button type="submit" className="btn btn-dark">Register</button>
          </div>
        </div>
      </form>
    </GuestLayout>
  );
}
