<x-laravel-generator::base-layout>

    <div class="p-2">

        <form class="form" @submit.prevent="store.post">

            <template v-for="table in store.tables" :key="table.id">

                <div style="margin-bottom: 20px;border: 1px solid #ccc;padding: 10px;" class="card">

                    <div class="d-flex justify-content-between">
                        <x-laravel-generator::input placeholder="Column Name" v-model="table.tableName"
                            ::id="table.tableName" />

                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Add Column
                            </button>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <span type="button" :data-table-id="table.tableId"
                                            @click="store.addEmptyColumn">
                                            Empty Column
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <span type="button" :data-table-id="table.tableId"
                                            @click="store.addStringColumn">
                                            String Column
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <span type="button" :data-table-id="table.tableId"
                                            @click="store.addEnumColumn">
                                            Enum Column
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <table class="table">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Primary</th>
                                <th>Nullable</th>
                                <th>Foreign</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template v-for="column in table.columns" :key="column.id">
                                <tr id="column.id">
                                    <td>
                                        <x-laravel-generator::input placeholder="Column Name" v-model="column.name"
                                            ::id="column.name" />
                                    </td>

                                    <td>
                                        <x-laravel-generator::input placeholder="Type" v-model="column.type" />
                                    </td>

                                    <td>
                                        <x-laravel-generator::checkbox v-show="column.hasOwnProperty('isPrimary')"
                                            v-model="column.isPrimary" />
                                    </td>

                                    <td>
                                        <x-laravel-generator::checkbox v-show="column.hasOwnProperty('isNullable')"
                                            v-model="column.isNullable" />
                                    </td>

                                    <td>
                                        <x-laravel-generator::checkbox v-show="column.hasOwnProperty('isForeign')"
                                            v-model="column.isForeign" />
                                    </td>
                                </tr>

                                <tr v-show="column.isForeign" style="">
                                    <td colspan="1" style="border: none"></td>

                                    <td style="border: none">References</td>

                                    <td style="border: none">On</td>
                                </tr>

                                <tr v-show="column.isForeign && column.foreign"style="">
                                    <td style="border: none" colspan="1"></td>

                                    <td style="border: none">
                                        <x-laravel-generator::input placeholder="Type"
                                            v-model="column.foreign.references" />
                                    </td>

                                    <td style="border: none">
                                        <x-laravel-generator::input placeholder="Type" v-model="column.foreign.on" />
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                </div>

            </template>

            <button type="submit" class="btn btn-primary">Submit</button>

        </form>

    </div>
</x-laravel-generator::base-layout>
