/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  async redirects() {
    return [
      {
        source: '/hardware',
        destination: '/products',
        permanent: true,
      },
    ];
  },
};

export default nextConfig;
