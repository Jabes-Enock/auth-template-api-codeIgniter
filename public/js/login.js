var profile = async () => {
  const response = await axiosInstance.get('/auth/profile')
  console.log(response)
}

profile()

